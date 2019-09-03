<style src="./Main.css" />
<script src="./Main.js" />

<template>
  <div id='main'>
    <GmapMap id="maps" ref='mapRef' @idle="idle" :center="{ lat: 23.77133806905457, lng: 120.70937982351438 }" map-type-id="ms" :zoom='7' :options="{ clickableIcons: false, disableDefaultUI: true, gestureHandling: 'greedy' }">
      <OAMarker v-for="(marker, index) in modifymarkers" :key="index" :alignment="'center'" :marker="{ lat: marker.lat, lng: marker.lng }" :class="['course' + marker.course, 'speed' + marker.speed]" @click.native='click(marker.id)'></OAMarker>
      <GmapPolyline :path='path' :options="{ strokeWeight: 5, strokeColor: 'rgba(66, 134, 244, .75)'}" />
    </GmapMap>

    <div id='levels'>
      <span v-for='(speed, index) in speeds' :key='index'>{{ speed }}+</span>
    </div>

  </div>
</template>
