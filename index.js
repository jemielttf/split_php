(function () {
	'use strict';

	function init(e) {
		init__form();
		init__file_select();
	}

	function init__file_select() {
		const file_select = document.querySelectorAll('input[type=file]');

		const on_file_select = event => {
			const 	target 	= event.currentTarget,
					preview	= document.querySelector(`input[type=text][name=filename_${target.name}]`),
					file	= target.files[0];

			console.log(target, preview, file);

			preview.value = file.name;
		};

		file_select.forEach(input => {
			input.addEventListener('change', on_file_select, false);
		});
	}

	function init__form() {
		const	form 	= document.querySelector('#PDF_Split'),
				submit	= document.querySelector('#Submit'),
				result	= document.querySelector('.result div iframe');

		
		submit.addEventListener('click', async event => {
			event.preventDefault();

			result.addEventListener('load', send_form, false);
			result.contentDocument.location.replace('./progress.html?v=0.0.2');
		}, false);

		function send_form(event) {
			console.log(`IFRAME FIRE : ${event.type}`);

			result.removeEventListener('load', send_form, false);

			form.submit();
		}
	}

	async function post_data(url = '', data) {
		const response = await fetch(url, {
			method: 'POST',
			body: 	data
		});

		return response.json();
	}

	window.addEventListener('DOMContentLoaded', init, false);
})();