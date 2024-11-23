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
		}
	}

	deleteRecord(event) {
		event.preventDefault();
		let target = event.target;
		let record = target.dataset.record;
		console.log(record);
	}

	editRecord(event) {
		event.preventDefault();
		let targetElement = event.target;
		let record = JSON.parse(targetElement.dataset.record);
		let modal = targetElement.dataset.bsTarget;
		let form = document.querySelector(modal).querySelector('form');
		console.log(record);
		form.querySelector('select[name="type"]').value = record.type;
		form.querySelector('select[name="type"]').dispatchEvent(new Event('change'));
		form.querySelector('input[name="name"]').value = record.name;
		form.querySelector('input[name="ttl"]').value = record.ttl;
		switch(record.type) {
			case 'A':
			case 'AAAA':
			case 'ALIAS':
			case 'CNAME':
			case 'NS':
				form.querySelector('input[name="target"]').value = record.content;
				break;
			case 'CAA':
				[record.flag, record.tag, record.content] = record.content.split(' ');
				form.querySelector('input[name="flag"]').value = record.flag;
				form.querySelector('input[name="tag"]').value = record.tag;
				form.querySelector('input[name="content"]').value = record.content.replace(/^"+|"+$/g, '');
				break;
			case 'MX':
				[record.priority, record.target] = record.content.split(' ');
				form.querySelector('input[name="priority"]').value = record.priority;
				form.querySelector('input[name="target"]').value = record.target;
				break;
			case 'SRV':
				[record.priority, record.weight, record.port, record.target] = record.content.split(' ');
				form.querySelector('input[name="priority"]').value = record.priority;
				form.querySelector('input[name="weight"]').value = record.weight;
				form.querySelector('input[name="port"]').value = record.port;
				form.querySelector('input[name="target"]').value = record.target;
				break;
			case 'SSHFP':
				[record.class, record.algo, record.content] = record.content.replace(/^"+|"+$/g, '').split(' ');
				form.querySelector('input[name="class"]').value = record.class;
				form.querySelector('input[name="algo"]').value = record.algo;
				form.querySelector('input[name="content"]').value = record.content;
				break;
			case 'TLSA':
				[record.usage, record.selector, record.matching_type, record.content] = record.content.replace(/^"+|"+$/g, '').split(' ');
				form.querySelector('input[name="usage"]').value = record.usage;
				form.querySelector('input[name="selector"]').value = record.selector;
				form.querySelector('input[name="matching-type"]').value = record.matching_type;
				form.querySelector('input[name="content"]').value = record.content;
				break;
			case 'OPENPGPKEY':
			case 'TXT':
				form.querySelector('input[name="content"]').value = record.content.replace(/^"+|"+$/g, '');
		}
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
			MX: ['priority', 'target'],
			NS: ['target'],
			SRV: ['priority', 'weight', 'port', 'target'],
			SSHFP: ['class', 'algo', 'content'],
			OPENPGPKEY: ['content'],
			TLSA: ['usage', 'selector', 'matching-type', 'content'],
			TXT: ['content']
		}[type] || [];

		showFields.forEach(field => form.querySelector(`#fg-${field}`).classList.remove('d-none'));
	}
}
