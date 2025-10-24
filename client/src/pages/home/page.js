import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils";

export async function HomePage() {
    const fragment = htmlToFragment(template);
    
    // Personnalisation simple et sûre
    try {
        const userDataString = sessionStorage.getItem('user');
        if (userDataString) {
            const user = JSON.parse(userDataString);
            const userName = user.firstName || user.username;
            
            if (userName) {
                const currentHour = new Date().getHours();
                let greeting = "BONJOUR";
                
                if (currentHour >= 18 || currentHour < 5) {
                    greeting = "BONSOIR";
                } else if (currentHour >= 12 && currentHour < 18) {
                    greeting = "BON APRÈS-MIDI";
                }
                
                const welcomeTitle = fragment.querySelector('#welcomeTitle');
                const welcomeSubtitle = fragment.querySelector('#welcomeSubtitle');
                
                if (welcomeTitle) {
                    welcomeTitle.textContent = `${greeting} ${userName.toUpperCase()} ! 🎄`;
                }
                if (welcomeSubtitle) {
                    welcomeSubtitle.textContent = `Bienvenue dans notre univers de Noël`;
                }
            }
        }
    } catch (error) {
        console.log('Personnalisation ignorée:', error);
        // En cas d'erreur, on continue sans personnalisation
    }
    
    return fragment;
}
