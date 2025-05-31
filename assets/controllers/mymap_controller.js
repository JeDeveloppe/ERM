// assets/controllers/mymap_controller.js

import { Controller } from '@hotwired/stimulus';
import L from 'leaflet';

export default class extends Controller {

    // PROPRIÉTÉ DU CONTRÔLEUR POUR STOCKER TEMPORAIREMENT LA COULEUR
    tempMarkerColor = null;

    connect() {
        this.element.addEventListener('ux:map:marker:before-create', this._onMarkerBeforeCreate);
        this.element.addEventListener('ux:map:marker:after-create', this._onMarkerAfterCreate);
        this.element.addEventListener('ux:map:info-window:before-create', this._onInfoWindowBeforeCreate);
        this.element.addEventListener('ux:map:polygon:before-create', this._onPolygonBeforeCreate);
        this.element.addEventListener('ux:map:polyline:before-create', this._onPolylineBeforeCreate);
    }

    _onMarkerBeforeCreate(event) {
        const definition = event.detail.definition;

        if (!definition) {
            return;
        }

        const markerExtra = definition.extra;
        const markerColor = (markerExtra && markerExtra.markerColor) ? markerExtra.markerColor : '#000000';

        // STOCKAGE DE LA COULEUR DANS LA PROPRIÉTÉ DU CONTRÔLEUR
        this.tempMarkerColor = markerColor;

        // Autres options Leaflet standard
        definition.rawOptions = definition.rawOptions || {};
        definition.rawOptions.riseOnHover = true;

        event.detail.definition = definition;
    }

    _onMarkerAfterCreate(event) {
        const markerInstance = event.detail.marker;

        if (!markerInstance) {
            return;
        }

        // Récupération de la couleur stockée depuis la propriété du contrôleur
        const markerColor = this.tempMarkerColor;

        if (markerColor) {
            setTimeout(() => {
                const markerElement = markerInstance.getElement();

                if (!markerElement) {
                    return;
                }

                const svgElement = markerElement.querySelector('svg');

                if (svgElement) {
                    // Applique la propriété 'color' sur l'élément SVG (pour currentColor)
                    svgElement.style.setProperty('color', markerColor, 'important');

                    const pathElement = svgElement.querySelector('path');
                    if (pathElement) {
                        // Applique aussi 'fill' directement sur le path pour une robustesse maximale
                        pathElement.style.setProperty('fill', markerColor, 'important');
                    }
                }
            }, 0);
        }

        // Réinitialiser la couleur pour le prochain marqueur (si vous avez plusieurs marqueurs)
        this.tempMarkerColor = null;
    }

    _onInfoWindowBeforeCreate(event) {
        // Logique de l'InfoWindow si nécessaire
    }

    _onPolygonBeforeCreate(event) {
        // Logique du Polygon si nécessaire
    }

    _onPolylineBeforeCreate(event) {
        // Logique de la Polyline si nécessaire
    }
}