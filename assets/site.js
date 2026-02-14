import './stimulus.js';

// 1. Importation du JS de Bootstrap (depuis tes vendors/importmap)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// 2. Importation de TON thème CSS (depuis ton dossier assets)
import './styles/css/bootstrap.min.css'; 
import './styles/css/site.css';

console.log("🚀 JS chargé et Thème personnalisé importé");

const setupAlerts = () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            } catch (e) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }
        }, 3000);
    });
};

document.addEventListener('turbo:load', setupAlerts);
if (document.readyState !== 'loading') setupAlerts();
else document.addEventListener('DOMContentLoaded', setupAlerts);