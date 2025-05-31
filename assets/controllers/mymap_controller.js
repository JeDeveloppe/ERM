// assets/controllers/mymap_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    markerColorMap = new Map();

    connect() {
        this.element.addEventListener('ux:map:pre-connect', this._onPreConnect.bind(this));
        this.element.addEventListener('ux:map:connect', this._onConnect.bind(this));
        this.element.addEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate.bind(this));
        this.element.addEventListener('ux:map:marker:after-create', this._onMarkerAfterCreate.bind(this));
        this.element.addEventListener('ux:map:info-window:before-create', this._onInfoWindowBeforeCreate.bind(this));
        this.element.addEventListener('ux:map:info-window:after-create', this._onInfoWindowAfterCreate.bind(this));
        this.element.addEventListener('ux:map:polygon:before-create', this._onPolygonBeforeCreate.bind(this));
        this.element.addEventListener('ux:map:polygon:after-create', this._onPolygonAfterCreate.bind(this));
        this.element.addEventListener('ux:map:polyline:before-create', this._onPolylineBeforeCreate.bind(this));
        this.element.addEventListener('ux:map:polyline:after-create', this._onPolylineAfterCreate.bind(this));
    }

    disconnect() {
        this.element.removeEventListener('ux:map:pre-connect', this._onPreConnect.bind(this));
        this.element.removeEventListener('ux:map:connect', this._onConnect.bind(this));
        this.element.removeEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate.bind(this));
        this.element.removeEventListener('ux:map:marker:after-create', this._onMarkerAfterCreate.bind(this));
        this.element.removeEventListener('ux:map:info-window:before-create', this._onInfoWindowBeforeCreate.bind(this));
        this.element.removeEventListener('ux:map:info-window:after-create', this._onInfoWindowAfterCreate.bind(this));
        this.element.removeEventListener('ux:map:polygon:before-create', this._onPolygonBeforeCreate.bind(this));
        this.element.removeEventListener('ux:map:polygon:after-create', this._onPolygonAfterCreate.bind(this));
        this.element.removeEventListener('ux:map:polyline:before-create', this._onPolylineBeforeCreate.bind(this));
        this.element.removeEventListener('ux:map:polyline:after-create', this._onPolylineAfterCreate.bind(this));
    }

    _onPreConnect(event) { }
    _onConnect(event) { }

    _onMarkerBeforeCreate(event) {
        const definition = event.detail.definition;

        if (definition && definition['@id'] && definition.extra && definition.extra.markerColor) {
            this.markerColorMap.set(definition['@id'], definition.extra.markerColor);
        }

        // Assurez-vous que l'ID est passé aux rawOptions de Leaflet
        // pour qu'il soit accessible via markerInstance.options['@id']
        definition.rawOptions = definition.rawOptions || {};
        definition.rawOptions['@id'] = definition['@id'];
    }

    _onMarkerAfterCreate(event) {
        const markerInstance = event.detail.marker;
        // Récupère l'ID depuis les options de l'instance Leaflet du marqueur
        const markerId = markerInstance.options ? markerInstance.options['@id'] : undefined;

        if (!markerInstance || !markerId) {
            return;
        }

        const markerColor = this.markerColorMap.get(markerId);

        if (markerColor) {
            setTimeout(() => {
                const markerElement = markerInstance.getElement();

                if (!markerElement) {
                    return;
                }

                const svgElement = markerElement.querySelector('svg');
                if (svgElement) {
                    svgElement.style.setProperty('color', markerColor, 'important');
                    const pathElement = svgElement.querySelector('path');
                    if (pathElement) {
                        pathElement.style.setProperty('fill', markerColor, 'important');
                    }
                }
            }, 0);
        }

        this.markerColorMap.delete(markerId);
    }

    _onInfoWindowBeforeCreate(event) { }
    _onInfoWindowAfterCreate(event) { }
    _onPolygonBeforeCreate(event) { }
    _onPolygonAfterCreate(event) { }
    _onPolylineBeforeCreate(event) { }
    _onPolylineAfterCreate(event) { }
}