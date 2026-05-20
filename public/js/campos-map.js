(function () {
  var state = {
    el: null,
    fields: [],
    markers: {},
    map: null,
    provider: 'leaflet'
  };

  function readFields(el) {
    try {
      return JSON.parse(el.getAttribute('data-fields') || '[]').filter(function (field) {
        return Number(field.latitude) && Number(field.longitude);
      });
    } catch (error) {
      return [];
    }
  }

  function popupHtml(field) {
    return '<div class="map-info-window"><strong>' + field.name + '</strong><p>' +
      (field.address || field.city || 'Ceuta') + '</p><p>' +
      (field.description || 'Campo deportivo de Ceuta.') + '</p></div>';
  }

  function selectCard(id) {
    document.querySelectorAll('[data-field-card]').forEach(function (card) {
      card.classList.toggle('is-selected', Number(card.getAttribute('data-field-card')) === Number(id));
    });
  }

  function initGoogle() {
    if (!state.el || typeof google === 'undefined' || !google.maps) return false;
    state.map = new google.maps.Map(state.el, {
      zoom: 13,
      center: { lat: 35.8894, lng: -5.3198 },
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: true
    });
    var infoWindow = new google.maps.InfoWindow();
    state.fields.forEach(function (field) {
      var marker = new google.maps.Marker({
        position: { lat: Number(field.latitude), lng: Number(field.longitude) },
        map: state.map,
        title: field.name
      });
      state.markers[field.id] = marker;
      marker.addListener('click', function () {
        infoWindow.setContent(popupHtml(field));
        infoWindow.open(state.map, marker);
        selectCard(field.id);
      });
    });
    return true;
  }

  function initLeaflet() {
    if (!state.el || typeof L === 'undefined') return false;
    state.map = L.map(state.el).setView([35.8894, -5.3198], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(state.map);
    state.fields.forEach(function (field) {
      var marker = L.marker([Number(field.latitude), Number(field.longitude)])
        .addTo(state.map)
        .bindPopup(popupHtml(field));
      marker.on('click', function () { selectCard(field.id); });
      state.markers[field.id] = marker;
    });
    // fp-fade animation lasts 600ms; invalidateSize after it ends so Leaflet
    // recalculates bounds once the CSS transform on <main> has settled.
    setTimeout(function () { state.map.invalidateSize(); }, 700);
    return true;
  }

  function focusField(fieldId) {
    var field = state.fields.find(function (item) { return Number(item.id) === Number(fieldId); });
    if (!field || !state.map) return;
    selectCard(field.id);
    if (state.provider === 'google' && state.markers[field.id]) {
      state.map.panTo({ lat: Number(field.latitude), lng: Number(field.longitude) });
      google.maps.event.trigger(state.markers[field.id], 'click');
    } else if (state.markers[field.id]) {
      state.map.setView([Number(field.latitude), Number(field.longitude)], 15);
      state.markers[field.id].openPopup();
    }
  }

  function bindCards() {
    document.querySelectorAll('[data-field-card]').forEach(function (card) {
      card.addEventListener('click', function (event) {
        if (event.target.closest('a')) return;
        focusField(card.getAttribute('data-field-card'));
      });
    });
  }

  function init() {
    state.el = document.getElementById('ceuta-map');
    if (!state.el) return;
    state.fields = readFields(state.el);
    state.provider = state.el.getAttribute('data-map-provider') || 'leaflet';
    bindCards();
    if (state.provider === 'google' && initGoogle()) return;
    state.provider = 'leaflet';
    initLeaflet();
  }

  window.initCeutaMap = function () {
    if (state.el && state.provider === 'google') {
      initGoogle();
    }
  };

  document.addEventListener('DOMContentLoaded', init);
})();
