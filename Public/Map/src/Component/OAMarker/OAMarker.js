import * as VueGoogleMaps from "vue2-google-maps";

export default {
  name: 'OAMarkerComponent',
  mixins: [VueGoogleMaps.MapElementMixin],
  props: {
    marker: {
      type: Object,
      default: undefined
    },
    offsetX: {
      type: Number,
      default: 0
    },
    offsetY: {
      type: Number,
      default: 0
    },
    alignment: {
      type: String,
      default: "center"
    },
    zIndex: {
      type: Number,
      default: 50
    },
    className: {
      type: String,
      default: 'OAMarker'
    }
  },
  inject: {
    $clusterPromise: {
      default: null
    }
  },
  beforeCreate(options) {
    if (this.$clusterPromise) {
      options.map = null;
    }
    return this.$clusterPromise;
  },
  methods: {
    afterCreate(inst) {
      if (this.$clusterPromise) {
        this.$clusterPromise.then(co => {
          co.addMarker(inst);
          this.$clusterObject = co;
        });
      }
    }
  },
  data() {
    return {
      opacity: 0
    };
  },
  watch: {
    marker: {
       deep: true,
       handler(val){
         this.$mapPromise.then(map => this.$overlay.setPosition());
       }
    },
    zIndex(val) {
      this.$overlay.repaint()
    }
  },
  provide() {
    const self = this;
    return this.$mapPromise.then(map => {
      class Overlay extends google.maps.OverlayView {
        constructor(map) {
          super();
          this.setMap(map);
          this.draw = () => this.repaint();
          this.setPosition = () => this.repaint();
        }
        repaint() {
          const div = self.$el;
          const projection = this.getProjection();
          if (projection && div) {
            const posPixel = projection.fromLatLngToDivPixel(self.latLng);
            let x, y;
            
            switch (self.alignment) {
              case "top":
                x = posPixel.x - div.offsetWidth / 2;
                y = posPixel.y - div.offsetHeight;
                break;
              case "bottom":
                x = posPixel.x - div.offsetWidth / 2;
                y = posPixel.y;
                break;
              case "left":
                x = posPixel.x - div.offsetWidth;
                y = posPixel.y - div.offsetHeight / 2;
                break;
              case "right":
                x = posPixel.x;
                y = posPixel.y - div.offsetHeight / 2;
                break;
              case "center":
                x = posPixel.x - div.offsetWidth / 2;
                y = posPixel.y - div.offsetHeight / 2;
                break;
              case "topleft":
              case "lefttop":
                x = posPixel.x - div.offsetWidth;
                y = posPixel.y - div.offsetHeight;
                break;
              case "topright":
              case "righttop":
                x = posPixel.x;
                y = posPixel.y - div.offsetHeight;
                break;
              case "bottomleft":
              case "leftop":
                x = posPixel.x - div.offsetWidth;
                y = posPixel.y;
                break;
              case "bottomright":
              case "rightbottom":
                x = posPixel.x;
                y = posPixel.y;
                break;
              default:
                throw new Error("Invalid alignment type of custom marker!");
                break;
            }
            div.style.left = x + self.offsetX + "px";
            div.style.top = y + self.offsetY + "px";
            div.style["z-index"] = self.zIndex;
          }
        }
        onAdd() {
          const div = self.$el;
          const panes = this.getPanes();
          div.style.position = "absolute";
          div.style.display = "inline-block";
          div.style.zIndex = self.zIndex;
          panes.overlayLayer.appendChild(div);
          panes.overlayMouseTarget.appendChild(div);
          this.getDraggable = () => false;
          this.getPosition = () => {
            return new google.maps.LatLng(self.lat, self.lng);
          };
          self.afterCreate(this);
        }
        onRemove() {
          self.$el.remove();
        }
      }
      this.$overlay = new Overlay(map);
      var that = this
      setTimeout(() => {
          if (that.$overlay) {
            that.$overlay.repaint();
            that.opacity = 1;
          }
      }, 100);
    });
  },
  computed: {
    lat() {
      return parseFloat(
        isNaN(this.marker.lat) ? this.marker.latitude : this.marker.lat
      );
    },
    lng() {
      return parseFloat(
        isNaN(this.marker.lng) ? this.marker.longitude : this.marker.lng
      );
    },
    latLng() {
      if (this.marker instanceof google.maps.LatLng) {
          return this.marker;
      }
      return new google.maps.LatLng(this.lat, this.lng);
    }
  },
  destroyed() {
    this.$overlay.setMap(null);
    this.$overlay = undefined;
  }
}
