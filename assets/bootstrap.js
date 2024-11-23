import { startStimulusApp } from '@symfony/stimulus-bundle';
import ZoneEditorController from './controllers/zone_editor.js';

const app = startStimulusApp();
app.register('zone-editor', ZoneEditorController);
