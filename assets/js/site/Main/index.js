/**
 * @author      OA Wu <comdan66@gmail.com>
 * @copyright   Copyright (c) 2015 - 2018, OAF2E
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        https://www.ioa.tw/
 */

// Array.prototype.max = function (k) {
//   return Math.max.apply (null, this.column (k));
// };
window.gmc = function () { $(window).trigger ('gm'); };
function OAGM(t){this._div=null,this._option=Object.assign({className:"",top:0,left:0,width:32,height:32,html:"",map:null,position:null,css:{}},t),this._option.map&&this.setMap(this._option.map)}function initOAGM(){OAGM.prototype=new google.maps.OverlayView,Object.assign(OAGM.prototype,{setPoint:function(){if(!this._option.position)return this._div.style.left="-999px",void(this._div.style.top="-999px");var t=this.getProjection().fromLatLngToDivPixel(this._option.position);t&&(this._div.style.left=t.x-this._option.width/2+this._option.left+"px",this._div.style.top=t.y-this._option.height/2+this._option.top+"px")},draw:function(){if(!this._div){for(var t in this._div=document.createElement("div"),this._div.style.position="absolute",this._div.className=this._option.className,this._div.style.width=this._option.width+"px",this._div.style.height=this._option.height+"px",this._div.innerHTML=this._option.html,this._option.css)"width"!=t&&"height"!=t&&"top"!=t&&"left"!=t&&"bottom"!=t&&"right"!=t&&(this._div.style[t]=this._option.css[t]);var i=this;google.maps.event.addDomListener(this._div,"click",function(t){t.stopPropagation&&t.stopPropagation(),google.maps.event.trigger(i,"click")}),this.getPanes().overlayImage.appendChild(this._div)}this.setPoint()},remove:function(){return this._div&&(this._div.parentNode.removeChild(this._div),this._div=null),this},setWidth:function(t){return this._div&&(this._option.width=t,this._div.style.width=this._option.width+"px",this.setPoint()),this},setHeight:function(t){return this._div&&(this._option.height=t,this._div.style.height=this._option.height+"px",this.setPoint()),this},setTop:function(t){return this._div&&(this._option.top=t,this._div.style.top=this._option.top+"px",this.setPoint()),this},setLeft:function(t){return this._div&&(this._option.left=t,this._div.style.left=this._option.left+"px",this.setPoint()),this},setHtml:function(t){return this._div&&(this._option.html=t,this._div.innerHTML=this._option.html),this},setCss:function(t){if(!this._div)return this;for(var i in this._option.css=t,this._option.css)"width"!=i&&"height"!=i&&"top"!=i&&"left"!=i&&"bottom"!=i&&"right"!=i&&(this._div.style[i]=this._option.css[i]);return this},setClassName:function(t){return this._div&&(this._option.className=t,this._div.className=this._option.className),this},getClassName:function(){return this._option.className},setPosition:function(t){return this.map&&(this._option.position=t,this.setPoint()),this},getPosition:function(){return this._option.position}})}
function ful (l) { return (l / 1000).toFixed (2); }
$(function () {
  var $body = $('body');
  var _gmap = null;
  var _vp = [];
  var _ps = [];
  var _ms = [];
  var _ter = null;
  var _max = 0;
  var $_cs = null;
  var $_length = null;
  var $_duration = null;
  var _cs = ['#CCDDFF', '#99BBFF', '#5599FF', '#0066FF', '#0044BB', '#003C9D', '#003377', '#550088', '#770077'];


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

  function path (p) {
    _vp = _vp.map (function (t) { t instanceof google.maps.Polyline && t.setMap (null); t = null; return null; }).filter (function (t) { return t; });
    p.forEach (function (t) {_max = t._v.s > _max ? t._v.s : _max; });

    for (var i = 0; i < p.length; i++) {
      if (!i) continue;
      
      var s = parseInt ((_cs.length / _max) * p[i]._v.s, 10);
      
      _vp.push (new google.maps.Polyline ({
        map: _gmap,
        strokeColor: _cs[s] ? _cs[s] : _cs[_cs.length - 1],
        strokeWeight: 5,
        path: [p[i - 1], p[i]]
      }));
    }

    $_cs.empty ().append (_cs.map (function (c, i) {
      return $('<div />').text (parseInt ((_max < 0 ? 0 : _max / _cs.length) * i, 10)).css ({ 'background-color': c });
    }));
  }

  var Clustering = function (opts) {
    // this.lats = [];
    // this.lngs = [];
    // this.merges = [];

    this.uses = [];
    this.tmp = [];

    this.opts = Object.assign ({
      map: null,
      unit: 3,
      useLine: false,
      middle: true,

      latKey: 'a',
      lngKey: 'n',
      varKey: null,
      markersKey: null,

    }, opts);
  };

  Object.assign (
    Clustering.prototype, {
      clean: function () {
        this.uses = [];
        this.tmp = [];
      },
      markers: function (arr) {
        if (!this.opts.map)
          return [];

        var that = this,
            z = this.opts.map.zoom,
            i = 0,
            j = 0,
            c = arr.length;

        that.clean ();

        for (; i < c; i++) {
          if (that.uses[i])
            continue;

          that.tmp[i] = {
            m: [arr[i]],
            a: arr[i][that.opts.latKey],
            n: arr[i][that.opts.lngKey],
          };
          that.uses[i] = true;

          for (j = i + 1; j < c; j++) {
            if (that.uses[j])
              continue;

            if ((30 / Math.pow (2, z)) / that.opts.unit <= Math.max (Math.abs (arr[i][that.opts.latKey] - arr[j][that.opts.latKey]), Math.abs (arr[i][that.opts.lngKey] - arr[j][that.opts.lngKey])))
              if (that.opts.useLine)
                break;
              else
                continue;

            that.uses[j] = true;
            that.tmp[i].m.push (arr[j]);
          }
        }


        var ms = [];

        that.tmp.forEach (function (t, i) {

          var tmp = that.opts.middle ?
            new google.maps.LatLng (t.m.map (function (u) { return u[that.opts.latKey]; }).reduce (function (a, b) { return a + b; }) / t.m.length, t.m.map (function (u) { return u[that.opts.lngKey]; }).reduce (function (a, b) { return a + b; }) / t.m.length) :
            new google.maps.LatLng (t.a, t.n);

          if (that.opts.markersKey !== null)
            tmp[that.opts.markersKey] = t;

          if (that.opts.varKey !== null)
            tmp[that.opts.varKey] = arr[i];

          ms.push (tmp);
        });

        that.clean ();

        return ms;
      }
    }
  );

  // 座標陣列, 單位[3], 紀錄陣列, 線性, 合併
  function calc (v, k, m, l, s) {
    k = k ? k : 3;
    var z = _gmap.zoom, u = [], zs = [], i = 0, j = 0, c = v.length;

    for (; i < c; i++) {
      if (v[i]._h)
        continue;

      zs[i] = {};
      zs[i]._m = [v[i]];
      zs[i]._v = v[i];
      v[i]._h = true;

      for (j = i + 1; j < c; j++) {
        if (v[j]._h)
          continue;

        if ((30 / Math.pow (2, z)) / k <= Math.max (Math.abs (v[i].a - v[j].a), Math.abs (v[i].n - v[j].n)))
          if (l)
            break;
          else
            continue;

        v[j]._h = true;
        zs[i]._m.push (v[j]);
      }
    }

    for (i = 0; i < c; i++)
      v[i]._h = false;

    u = [];

    zs.forEach (function (t) {
      var x = new google.maps.LatLng (t._v.a, t._v.n);

      if (s) {
        var a = t._m.map (function (u) { return u.a; }).reduce (function (a, b) { return a + b; }) / t._m.length;
        var n = t._m.map (function (u) { return u.n; }).reduce (function (a, b) { return a + b; }) / t._m.length;
        
        x = new google.maps.LatLng (a, n);
      }
      if (m) x._m = t._m;

      x._v = t._v;
      u.push (x);
    });

    return u;
  }

  function ajax (url, first) {
    $.ajax ({
      url: url,
      async: true, cache: false, dataType: 'json', type: 'GET'
    })
    .done (function (result) {
      _ps = result.p.map (function (t) { return t; });
      $_length.addClass ('s').text (ful (result.l));
      $_duration.addClass ('s').text (result.d);

      rePath (first);

    }.bind ($(this)))
    .fail (function (result) {
    }.bind ($(this)));
  }

  function rePath (f) {
    
    path (new Clustering ({
      map: _gmap,
      unit: 2,
      middle: true,
      useLine: true,
      varKey: '_v'
    }).markers (_ps));
    
    _ms = _ms.map (function (t) { t instanceof OAGM && t.setMap (null); t = null; return null; }).filter (function (t) { return t; });    
    _ms = new Clustering ({
      map: _gmap,
      unit: 0.8,
      middle: true,
      useLine: true,
      varKey: '_v'
    }).markers (_ps);

    _ms = _ms.map (function (t) {
      var s = parseInt ((_cs.length / _max) * t._v.s, 10);

      return new OAGM ({
        map: _gmap,
        position: t,
        width: 20,
        height: 20,
        className: 'point r' + parseInt (t._v.c, 10),
        css: {
          'border': '3px solid ' + (_cs[s] ? _cs[s] : _cs[_cs.length - 1]),
          'color': _cs[s] ? _cs[s] : _cs[_cs.length - 1]
        }});
    });

    if (!f || !_ms.length)
      return;


    var bounds = new google.maps.LatLngBounds ();
    _ms.forEach (function (t, i) {
      bounds.extend (t.getPosition ());
    });
    _gmap.fitBounds (bounds);
  }

  oaGmap.addFunc (function () {
    initOAGM ();

    var $maps = $('#maps');
    var $gmap = $('<div />').addClass ('gmap').appendTo ($maps);
    var $zoom = $('<div />').addClass ('zoom').append ($('<a />').addClass ('icon-02')).append ($('<a />').addClass ('icon-01')).appendTo ($maps);
    $_cs  = $('<div />').addClass ('colors').appendTo ($maps);
    $_length  = $('<div />').addClass ('length').appendTo ($maps);
    $_duration  = $('<div />').addClass ('duration').appendTo ($maps);

    var position = new google.maps.LatLng (23.79539759, 120.88256835);
    _gmap = new google.maps.Map ($gmap.get (0), { zoom: 10, clickableIcons: false, disableDefaultUI: true, gestureHandling: 'greedy', center: position });

    _gmap.mapTypes.set ('style1', new google.maps.StyledMapType ([{featureType: 'administrative.land_parcel', elementType: 'labels', stylers: [{visibility: 'on'}]}, {featureType: 'poi', elementType: 'labels.text', stylers: [{visibility: 'off'}]}, {featureType: 'poi.business', stylers: [{visibility: 'on'}]}, {featureType: 'poi.park', elementType: 'labels.text', stylers: [{visibility: 'on'}]}, {featureType: 'road.local', elementType: 'labels', stylers: [{visibility: 'on'}]}]));
    _gmap.setMapTypeId ('style1');
  
    $zoom.find ('a').click (function () { _gmap.setZoom (_gmap.zoom + ($(this).index () ? -1 : 1)); });

    // var a = new google.maps.Polyline ({
    //   map: _gmap,
    //   strokeColor: 'rgba(66, 133, 244, 1.00)',
    //   strokeWeight: 3,
    // });
    // console.error ();
    

    ajax ($maps.data ('url'), true);
    setInterval (ajax.bind (this, $maps.data ('url'), false), 1000 * 10);

    _gmap.addListener ('zoom_changed', function () {
      clearTimeout (_ter);
      _ter = setTimeout (rePath, 350);
    });
  });

  window.oaGmap.runFuncs ();
});