import Hello from '@/Component/Hello/Hello.vue'
import OAMarker from '@/Component/OAMarker/OAMarker.vue'
import axios from 'axios'
import {gmapApi} from 'vue2-google-maps'

export default {
  name: 'MainView',
  components: {
    Hello,
    OAMarker
  },
  mounted () {
    this.$refs.mapRef.$mapPromise.then(map => {
      map.mapTypes.set('ms', new google.maps.StyledMapType([{stylers: [{gamma: 0}, {weight: 0.75}] }, {featureType: 'all', stylers: [{ visibility: 'on' }]}, {featureType: 'administrative', stylers: [{ visibility: 'on' }]}, {featureType: 'landscape', stylers: [{ visibility: 'on' }]}, {featureType: 'poi', stylers: [{ visibility: 'on' }]}, {featureType: 'road', stylers: [{ visibility: 'simplified' }]}, {featureType: 'road.arterial', stylers: [{ visibility: 'on' }]}, {featureType: 'transit', stylers: [{ visibility: 'on' }]}, {featureType: 'water', stylers: [{ color: '#b3d1ff', visibility: 'on' }]}, {elementType: "labels.icon", stylers:[{ visibility: 'off' }]}]));
      // map.panTo({lat: 37.4131601200, lng: -122.2069144300})
    })
    this.loadData()
    setInterval(this.loadData, 30 * 1000)
  },
  data () {
    return {
      timer: null,
      oriMarkers: [],
      modifymarkers: [],
      path: [],
      speeds: [],
      isFirst: true,
      isloadingData: false,
    }
  },
  methods: {
    click (id) {
      console.error(id);
    },
    loadData () {
      var that = this
      if (that.isloadingData)
        return;
      else
        that.isloadingData = true

      axios.get(process.env.VUE_APP_API_BASE_URL + 'api/signals', {
        params: {
          deviceId: 1,
          eventId: 2
        }
      }).then(function (response) {
        var cnt = 10
        var tmps = response.data.map(t => t.speed)
        var min = Math.min(...tmps)
        var max = Math.max(...tmps)
        var unit = parseInt(Math.round((max - min + 1) / cnt), 10)

        that.speeds = [min]
        for (var i = 1; i <= cnt - 2; i++)
          if (min + i * unit < max)
            that.speeds.push(min + i * unit)
          else
            break
        that.speeds.push(max)

        that.oriMarkers = response.data.map(t => {
          for (var i in that.speeds)
            if (t.speed <= that.speeds[i])
              break
          ++i
          t.speed = i + ''
          return t
        })

      }).catch(function (error) {
      }).finally(function() {
        that.isloadingData = false
      })
    },
    cluster(oris, zoom, unit, lineStyle, closure) {
      if (!oris.length)
        return closure([])

      var tmps = {}
      var news = []
      
      for (var i = 0; i < oris.length; i++) {
        if (typeof tmps[i] !== 'undefined')
          continue

        tmps[i] = true
        var tmp = [oris[i]]

        for (var j = i + 1; j < oris.length; j++) {
          if (typeof tmps[j] !== 'undefined')
            if (lineStyle)
              break
            else
              continue

          var distance = Math.max(Math.abs(oris[i].lat - oris[j].lat), Math.abs(oris[i].lng - oris[j].lng))

          if (30 / Math.pow(2, zoom) / unit <= distance)
            if (lineStyle)
              break
            else
              continue

          tmps[j] = true
          tmp.push(oris[j])
        }

        news.push(tmp)
      }

      tmps = null
      return closure(news)
    },
    idle () {
      var that = this;

      that.idleTimer = setTimeout(function() {
        that.$refs.mapRef.$mapPromise.then(map => {
          
          that.cluster(that.oriMarkers, map.zoom, 1, true, function(markers) {
            that.modifymarkers = markers.map(t => t[0])
            that.path = that.modifymarkers.map(t => { return { lat: t.lat, lng: t.lng } })
            
            if (that.isFirst && gmapApi && that.oriMarkers.length > 2) {
              
              var bounds = new google.maps.LatLngBounds();
              for (var i in that.oriMarkers)
                bounds.extend(new google.maps.LatLng(that.oriMarkers[i].lat, that.oriMarkers[i].lng));
              map.fitBounds(bounds);

              that.isFirst = false
            }
          })
        })
      }, 500);
    }
  },
  computed: {
    idleTimer: {
      set (val) {
        clearTimeout(this.timer)
        this.timer = val
      },
      get () {
        return this.timer
      }
    }
  },
  watch: {
    oriMarkers () {
      this.idle()
    }
  }
}
