import './bootstrap.js';
import './styles/app.css';

import { shouldPerformTransition, performTransition } from "turbo-view-transitions";

document.addEventListener("turbo:before-render", (event) => {
	if (shouldPerformTransition()) {
		event.preventDefault();

		performTransition(document.body, event.detail.newBody, async () => {
			await event.detail.resume();
		});
	}
});

document.addEventListener("turbo:load", () => {
	if (shouldPerformTransition()) Turbo.cache.exemptPageFromCache();

	var event = new Event('DOMContentLoaded');
	document.dispatchEvent(event);
});