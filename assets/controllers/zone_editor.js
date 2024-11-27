import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

	getZone(){
		return this.element.dataset.zone;
	}

	getZoneType(){
		return this.element.dataset.zoneType;
	}

	addRecord(event) {
		event.preventDefault();
		let data = new FormData(event.target);

		let body = {
			zone: this.getZone(),
			zoneType: this.getZoneType(),
			record: this.prepareRecord(data)
		}

		this.sendRequest('/api/modify-record', 'POST', body);
	}

	deleteRecord(event) {
		event.preventDefault();
		let record = event.target.dataset.record;

		let body = {
			zone: this.getZone(),
			zoneType: this.getZoneType(),
			record: JSON.parse(record)
		}

		this.sendRequest('/api/modify-record', 'DELETE', body);
	}

	updateRecord(event) {
		event.preventDefault();
		let form = event.target;
		let data = new FormData(form);
		let record = JSON.parse(form.dataset.record);

		let body = {
			zone: this.getZone(),
			zoneType: this.getZoneType(),
			record: this.prepareRecord(data),
			old_record: record
		}

		this.sendRequest('/api/modify-record', 'PUT', body);
	}

	sendRequest(url, method, body) {
		fetch(url, {
			method: method,
			body: JSON.stringify(body),
		}).then(async response => {
			if (response.ok) {
				window.location.reload();
			}else{
				alert(await response.text());
			}
		});
	}

	editRecord(event) {
		event.preventDefault();
		let target = event.target;
		let record = JSON.parse(target.dataset.record);
		let modal = target.dataset.bsTarget;
		let form = document.querySelector(modal).querySelector('form');
		form.dataset.record = JSON.stringify(record);
		form.querySelector('select[name="type"]').value = record.type;
		form.querySelector('select[name="type"]').dispatchEvent(new Event('change'));
		form.querySelector('input[name="name"]').value = record.displayName;
		form.querySelector('input[name="ttl"]').value = record.ttl;
		switch(record.type) {
			case 'A':
			case 'AAAA':
			case 'ALIAS':
			case 'CNAME':
			case 'NS':
			case 'PTR':
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

	prepareRecord(data){
		let { type, name, ttl, target, flag, tag, priority, weight, port, content, algo, class: klass } = Object.fromEntries(data.entries());

		let recordContent = "";
		switch (type) {
			case 'A':
			case 'AAAA':
			case 'ALIAS':
			case 'CNAME':
			case 'NS':
			case 'PTR':
				recordContent = target;
				break;
			case 'CAA':
				recordContent = `${flag} ${tag} "${content}"`;
				break;
			case 'MX':
				recordContent = `${priority} ${target}`;
				break;
			case 'SRV':
				recordContent = `${priority} ${weight} ${port} ${target}`;
				break;
			case 'SSHFP':
				recordContent = `${klass} ${algo} ${content}`;
				break;
			case 'OPENPGPKEY':
			case 'TXT':
				recordContent = `"${content}"`;
		}

		let record = {
			type: type,
			name: name,
			ttl: ttl,
			content: recordContent
		}
		return record;
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
