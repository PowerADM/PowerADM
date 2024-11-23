import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
	addRecord(event) {
		event.preventDefault();
		let form = event.target;
		let data = new FormData(form);
		let { type, name, ttl, target, priority, weight, port, content, algo, class: klass } = Object.fromEntries(data.entries());

		switch (type) {
			case 'A':
			case 'AAAA':
			case 'ALIAS':
			case 'CNAME':
			case 'NS':
				console.log('Add record', type, name, ttl, target);
				break;
			case 'MX':
				console.log('Add record', type, name, ttl, priority, target);
				break;
			case 'SRV':
				console.log('Add record', type, name, ttl, priority, weight, port, target);
				break;
			case 'SSHFP':
				console.log('Add record', type, name, ttl, klass, algo, content);
				break;
			case 'OPENPGPKEY':
			case 'TXT':
				console.log('Add record', type, name, ttl, content);
				break;
		}
	}

	deleteRecord() {
		console.log('Delete record');
	}

	editRecord() {
		console.log('Edit record');
	}

	updateRecord(event) {
		event.preventDefault();
		let form = event.target;
		let data = new FormData(form);
		let { type, name, ttl, target, priority, weight, port, content, algo, class: klass } = Object.fromEntries(data.entries());

		switch (type) {
			case 'A':
			case 'AAAA':
			case 'ALIAS':
			case 'CNAME':
			case 'NS':
				console.log('Update record', type, name, ttl, target);
				break;
			case 'MX':
				console.log('Update record', type, name, ttl, priority, target);
				break;
			case 'SRV':
				console.log('Update record', type, name, ttl, priority, weight, port, target);
				break;
			case 'SSHFP':
				console.log('Update record', type, name, ttl, klass, algo, content);
				break;
			case 'OPENPGPKEY':
			case 'TXT':
				console.log('Update record', type, name, ttl, content);
				break;
		}
	}

	changeType(event) {
		let target = event.target;
		let type = target.value;
		let form = target.closest('form');
		let fields = ['flag', 'tag', 'class', 'algo', 'usage', 'selector', 'matching-type', 'priority', 'weight', 'port', 'content', 'target'];

		fields.forEach(field => form.querySelector(`#fg-${field}`).classList.add('d-none'));

		let showFields = {
			A: ['target'],
			AAAA: ['target'],
			ALIAS: ['target'],
			CAA: ['flag', 'tag', 'content'],
			CNAME: ['target'],
			NS: ['target'],
			MX: ['priority', 'target'],
			SRV: ['priority', 'weight', 'port', 'target'],
			SSHFP: ['class', 'algo', 'content'],
			OPENPGPKEY: ['content'],
			TLSA: ['usage', 'selector', 'matching-type', 'content'],
			TXT: ['content']
		}[type] || [];

		showFields.forEach(field => form.querySelector(`#fg-${field}`).classList.remove('d-none'));
	}
}
