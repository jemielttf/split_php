(function () {
	'use strict';

	function init(e) {
		// init__form();
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
				result	= document.querySelector('.result div');
		
		submit.addEventListener('click', async event => {
			event.preventDefault();

			const data = new FormData(form);
			console.log(data.get('start'), data.get('end'));

			post_data(form.action, data)
					.then(res => {
						result.textContent = '';
						result.insertAdjacentHTML('afterbegin', JSON.stringify(res));
					})
					.catch(error => console.error(error));
		}, false);
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