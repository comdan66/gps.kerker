/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

window.gmc = function () { $(window).trigger ('gm'); };

$(function () {
  var $body = $('body');

  window.oaGmap = {
    keys: ['AIzaSyApe66UP8VJNwSVufAi0rr9dpq7ON0Dq6Y'],
    funcs: [],
    loaded: false,
    init: function () {
      if (window.oaGmap.loaded) return false;
      window.oaGmap.loaded = true;
      window.oaGmap.funcs.forEach (function (t) { t (); });
    },
    runFuncs: function () {
      if (!this.funcs.length) return true;

      $(window).bind ('gm', window.oaGmap.init);
      var k = this.keys[Math.floor ((Math.random() * this.keys.length))], s = document.createElement ('script');
      s.setAttribute ('type', 'text/javascript');
      s.setAttribute ('src', 'https://maps.googleapis.com/maps/api/js?' + (k ? 'key=' + k + '&' : '') + 'language=zh-TW&libraries=visualization&callback=gmc');
      (document.getElementsByTagName ('head')[0] || document.documentElement).appendChild (s);
      s.onload = window.oaGmap.init;
    },
    addFunc: function (func) {
      this.funcs.push (func);
    }
  };

  var _vp = null;
  var _ms = {};
  
  function path () {
    var t = []; for (var a in _ms) t.push (_ms[a].p);
    _vp.setPath (t);
  }

  function calc (v, d) {
    var z = gmap.zoom, u = [];
    
    if (z > 15) {
      u = v.map (function (t) { var p = new google.maps.LatLng (t.a, t.n); p._c = 1; return p; });
    } else {
      var zs = [];

      for (var i = 0, c = v.length; i < c; i++) {
        if (v[i].h) continue;

        zs[i] = new google.maps.LatLng (v[i].a, v[i].n);
        zs[i]._c = 1;
        v[i].h = true;

        for (var j = 0; j < c; j++) {
          if (v[j].h) continue;

          d = Math.max (Math.abs (v[i].a - v[j].a), Math.abs (v[i].n - v[j].n));
          if ((30 / Math.pow (2, z)) / 3 > d) {
            v[j].h = true;
            zs[i]._c += 1;
          }
        }
      }

      for (var i = 0, c; i < c; i++) v[i].h = false;
      u = []; zs.forEach (function (t) { u.push (t); });
    }

    return u;
  }

  function ajax (url) {
      $.ajax ({
        url: url,
        async: true, cache: false, dataType: 'json', type: 'GET'
      })
      .done (function (result) {
        var bounds = new google.maps.LatLngBounds ();

        _vp.setPath (result.map (function (t) {
          var tmp = new google.maps.LatLng (t.lat, t.lng);
          bounds.extend (tmp);
          return tmp;
        }));

        _vp.map.fitBounds (bounds);

      }.bind ($(this)))
      .fail (function (result) {
      }.bind ($(this)));
  }

  oaGmap.addFunc (function () {
    var $maps = $('#maps');
    var $gmap = $('<div />').addClass ('gmap').appendTo ($maps);
    var $zoom = $('<div />').addClass ('zoom').append ($('<a />').text ('+')).append ($('<a />').text ('-')).appendTo ($maps);


    var position = new google.maps.LatLng (23.79539759, 120.88256835);
    var gmap = new google.maps.Map ($gmap.get (0), { zoom: 16, clickableIcons: false, disableDefaultUI: true, gestureHandling: 'greedy', center: position });

    gmap.mapTypes.set ('style1', new google.maps.StyledMapType ([{featureType: 'administrative.land_parcel', elementType: 'labels', stylers: [{visibility: 'on'}]}, {featureType: 'poi', elementType: 'labels.text', stylers: [{visibility: 'off'}]}, {featureType: 'poi.business', stylers: [{visibility: 'on'}]}, {featureType: 'poi.park', elementType: 'labels.text', stylers: [{visibility: 'on'}]}, {featureType: 'road.local', elementType: 'labels', stylers: [{visibility: 'on'}]}]));
    gmap.setMapTypeId ('style1');
  
    $zoom.find ('a').click (function () { gmap.setZoom (gmap.zoom + ($(this).index () ? -1 : 1)); });
    
    _vp = new google.maps.Polyline ({
      map: gmap,
      strokeColor: 'rgba(66, 133, 244, 1.00)',
      strokeWeight: 3,
    });
// console.error ();

    ajax ($maps.data ('url'));
    setInterval (ajax.bind (this, $maps.data ('url')), 1000 * 5);
    

    // $maps.data ('points').forEach (function (t) {
    //   var p = new google.maps.LatLng (t.lat, t.lng);
    //   var marker = new google.maps.Marker ({
    //     map: gmap,
    //     zIndex: 2,
    //     draggable: true,
    //     position: p
    //   });

    //   marker.addListener ('click', function () {
    //     $.ajax ({ url: '/api/set1/?id=' + t.id, async: true, cache: false, dataType: 'json', type: 'get' }).done (function (r) {
    //       marker.setMap (null);
    //       _ms[t.id] = null;
    //       delete _ms[t.id];
    //       path ();
    //     });
    //   });

    //   marker.addListener ('dragend', function (e) {
    //     $.ajax ({ url: '/api/set2/?id=' + t.id + '&lat=' + marker.position.lat () + '&lng=' + marker.position.lng (), async: true, cache: false, dataType: 'json', type: 'get' }).done (function (r) {
    //       _ms[t.id].p = marker.position;
    //       path ();
    //     });
    //   });

    //   _ms[t.id] = {
    //     p: p,
    //     m: marker,
    //   };
    // });


    // var bounds = new google.maps.LatLngBounds ();
    // var tmp = [];
    // for (var a in _ms)
    //   tmp.push (_ms[a].p);

    // tmp = tmp.reverse ();

    // for (var i = 0; i < tmp.length; i++)
    //   if (i < 10)
    //     bounds.extend (tmp[i]);

    // gmap.fitBounds (bounds);

    // path ();

    // setTimeout (function () { $full.click (); }, 500);
  });

  window.oaGmap.runFuncs ();
});