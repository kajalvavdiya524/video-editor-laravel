require('../bootstrap');
require('datatables');
require('ekko-lightbox');

var indexCheckedFiles = [];
var selected_files = [];
$.fn.dataTable.ext.order['number-list'] = function (settings, col) {
	return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
		var input = $(td).find('input');
		if (!input.length) return 0;
		var id = input.data('id');
		var result = (indexCheckedFiles.indexOf(id) > -1) ? 2 : 1;
		return result;
	});
}

$(document).ready(function () {
	localStorage.removeItem("selected_files");

	if ($('div.index-info').length) {
		setTimeout(function () {
			$('div.index-info').fadeOut('slow');
		}, 5000);
	}

	var dataTable = $('#files-index-table').DataTable({
		processing: true,
		serverSide: true,
		ajax: '/file/data',
		search: {
			regex: true
		},
		columns: [
			{ data: 'name', name: 'name' },
			{ data: 'product_name', name: 'product_name' },
			{ data: 'brand', name: 'brand' },
			{ data: 'status', name: 'status' },
			// { data: 'id', name: 'id' }
			{
				data: 'id',
				render: function (dataField) {
					// console.log(dataField);
					return (dataField != 0) ? '<input type="checkbox" class="file-select-checkbox" data-id="' + dataField + '">' : '';
					// return '<input type="hidden" name="id" value="' + dataField + '">';
				},
				// type: 'numeric',
				// "orderDataType":"number-list"
				sortable: false
			}
		],
		drawCallback: function (settings) {
			var api = this.api();
			// Output the data for the visible rows to the browser's console
			// console.log( api.rows( {page:'current'} ).data() );
			$('.file-select-checkbox').on('change', function (e) {
				var checked = $(this).is(':checked');
				var name = $(this).closest('tr').find('.sorting_1').text().trim().split('.')[0];
				if (!checked) {
					indexCheckedFiles = _.pull(indexCheckedFiles, $(this).data('id'));
					selected_files = _.pull(selected_files, name);
				} else {
					indexCheckedFiles.push($(this).data('id'));
					selected_files.push(name);
				}
				// console.log(indexCheckedFiles);
				updateDownloadBlock();
			});
			$('.file-select-checkbox').each(function (index, item) {
				if (indexCheckedFiles.indexOf($(item).data('id')) > -1) {
					$(item).prop('checked', true);
				}
			});
		},
		language: {
			paginate: {
				first: '<',
				last: '>',
				previous: '<',
				next: '>',
				search: 'Filter'
			}
		}
	});

	$('#files-index-table').on('draw.dt', function (e) {
		// console.log(e);
		updateGlobalDownloadCheckbox();
	});

	function updateDownloadBlock() {
		var downloadBlock = $('.download-block');
		// console.log(indexCheckedFiles);
		if (indexCheckedFiles.length) {
			downloadBlock.fadeIn();
			var fileString = (indexCheckedFiles.length > 1) ? 'files' : 'file';
			downloadBlock.find('p').html(indexCheckedFiles.length + ' ' + fileString + ' selected');
		} else {
			downloadBlock.fadeOut();
		}
		updateGlobalDownloadCheckbox();
	}

	function updateGlobalDownloadCheckbox() {
		// Update Download checkbox in the table header
		var checkboxes = $('table#files-index-table').find('tbody').find('input[type="checkbox"]');
		var checkedCheckboxes = $('table#files-index-table').find('tbody').find('input[type="checkbox"]:checked');
		// console.log(checkboxes);
		// console.log(checkedCheckboxes);
		var checked = (checkboxes.length) ? (checkboxes.length == checkedCheckboxes.length) : false;
		$('#global-download').prop('checked', checked);
	}

	$('#global-download').on('change', function (e) {
		var checked = $(this).is(':checked');
		$('table#files-index-table').find('tbody').find('input[type="checkbox"]').each(function (index, item) {
			$(item).prop('checked', checked).trigger('change');
		});
		updateDownloadBlock();
	});

	if ($('#files-index-table').length) {
		$('#reindex').on('click', function (e) {
			e.preventDefault();
			$(this).prop('disabled', true);
			$(this).html($(this).data('loading-text'));
			var form = $('<form method="post" action="/file/reindex" style="display:none"></form>');
			form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
			$(document.body).append(form);
			form.submit();
		});

		$('#export').on('click', function (e) {
			var form = $('<form method="post" action="/file/export" style="display:none"></form>');
			form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
			$(document.body).append(form);
			form.submit();
		});

		$('#generate-thumbnail').on('click', function (e) {
			e.preventDefault();
			$(this).prop('disabled', true);
			$(this).html($(this).data('loading-text'));
			var form = $('<form method="post" action="/file/generate_thumbnail" style="display:none"></form>');
			form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
			$(document.body).append(form);
			form.submit();
		});

		$('#re-generate-thumbnail').on('click', function (e) {
			e.preventDefault();

			Swal.fire({
				title: 'Are you sure you want to regenerate all thumbnails?',
				showCancelButton: true,
				confirmButtonText: 'Confirm Regenerate',
				cancelButtonText: 'Cancel',
				icon: 'warning'
			}).then((result) => {
				if (result.value) {
					$(this).prop('disabled', true);
					$(this).html($(this).data('loading-text'));
					var form = $('<form method="post" action="/file/re_generate_thumbnail" style="display:none"></form>');
					form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
					$(document.body).append(form);
					form.submit();
				}
			});
		});
	}

	$('#index-search-input').on('paste keyup', function (e) {//change removed
		var inputValue = $(this).val();
		if (inputValue.length > 2) {
			var lines = [];
			lines = inputValue.split(/\n/);
			lines = _.pull(lines, '');
			lines = lines.join('|');
			dataTable.search(lines, true, false).draw();
		} else {
			dataTable.search('').draw();
		}
	});

	function indexUnselectAll() {
		indexCheckedFiles = [];
		selected_files = [];
		$('#global-download').prop('checked', false).trigger('change');
	}

	$('#global-download-unselect').on('click', function (e) {
		indexUnselectAll();
	});

	$('form#files-search-form').on('submit', function (e) {
		e.preventDefault();
	});

	var searchInput = $('input[name="filename-search"]');
	if (searchInput.length) {
		searchInput.on('paste keyup', function (e) {
			// e.preventDefault();
			if ($(this).val().length < 3) return;
			var search = {
				search: searchInput.val()
			}
			axios.post('/file/suggest', search).then(function (response) {
				// console.log('then');
				var table = $('table#selected-files').find('tbody');
				var suggest = $('ul.suggest');
				suggest.html('');
				response.data.results.forEach(function (item) {
					console.log(item);
					suggest.append('<li data-id="' + item.id + '">' + item.name + '</li>');
				});
				suggest.find('li').on('click', function (e) {
					console.log(e);
					e.preventDefault();
					var tr = $('<tr><td><input type="checkbox" data-id="' + $(this).data('id') + '" checked></td><td>' + $(this).html() + '</td><td><span class="remove">&times;</span></td></tr>');
					if (!table.find('td[data-id="' + $(this).data('id') + '"]').length) {
						table.append(tr);
						updateDownloadButton();
					}
					$('.selected-block').fadeIn();
					tr.find('span.remove').on('click', function (e) {
						$(this).parents('tr').remove();
						if (!table.find('tr').length) {
							$('.selected-block').fadeOut();
						}
					});
					tr.find('input[type="checkbox"]').on('change', function (e) {
						updateDownloadButton();
					});
					suggest.fadeOut();
					$(this).remove();
				});
				suggest.fadeIn();
				// window.location.reload();
			}).catch(function (error) {
				console.log(error);
			});
		});
	}

	$('#global-checkbox').on('change', function (e) {
		var checked = $(this).is(':checked');
		$('table#selected-files').find('tbody').find('input[type="checkbox"]').each(function (index, item) {
			$(item).prop('checked', checked);
		});
		updateDownloadButton();
	});

	function updateDownloadButton() {
		var isChecked = $('table#selected-files').find('tbody').find('input[type="checkbox"]:checked');
		if (isChecked.length) {
			$('#download').prop('disabled', false);
		} else {
			$('#download').prop('disabled', true);
			$('#global-checkbox').prop('checked', false);
		}
	}

	$('#create-ads').on('click', function (e) {
		e.preventDefault();
		if (indexCheckedFiles.length == 0) {
			var files = [];
			var table = $('table#selected-files').find('tbody');
			// console.log(table);
			table.find('input[type="checkbox"]:checked').each(function (index, item) {
				files.push($(item).data('id'));
			});
		} else {
			files = indexCheckedFiles;
		}

		localStorage.setItem("selected_files", selected_files.join(' '));

		var form = $('<form method="post" action="/file/create_ads" style="display:none"></form>');
		form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
		form.append($('<input type="hidden" name="file_ids" id="file_ids" value="' + files.toString() + '" />'));
		$(document.body).append(form);
		form.submit();
	});

	$('#download').on('click', function (e) {
		e.preventDefault();
		if (indexCheckedFiles.length == 0) {
			var files = [];
			var table = $('table#selected-files').find('tbody');
			table.find('input[type="checkbox"]:checked').each(function (index, item) {
				files.push($(item).data('id'));
			});
		} else {
			files = indexCheckedFiles;
		}
		$(this).prop('disabled', true).html($(this).data('loading-text'));
		directDownloadRequest(files);
	});

	$('#preview').on('click', function (e) {
		e.preventDefault();
		if (indexCheckedFiles.length == 0) {
			var files = [];
			var table = $('table#files-index-table').find('tbody');
			table.find('input[type="checkbox"]').each(function (index, item) {
				files.push($(item).data('id'));
			});
		} else {
			files = indexCheckedFiles;
		}
		$(this).prop('disabled', true).html($(this).data('loading-text'));
		previewRequest(files);
	});

	function previewRequest(files) {
		axios({
			method: 'post',
			url: '/file/view',
			data: {
				file_ids: files
			}
		})
			.then(function (response) {
				var data = response.data;
				var anchors = [];
				$('#product-images').empty();
				for (var file of data.files) {
					var anchor = $(`<a href="${file.url}" class="product-image" data-footer="${file.name}" data-gallery="product-image-gallery"></a>`);
					anchors.push(anchor);
					$('#product-images').append(anchor);
				}
				anchors[0].ekkoLightbox({ alwaysShowClose: true, footer: true });
				$('#preview').prop('disabled', false).html($('#preview').data('text'));
			})
			.catch(function (response) {
				$('#preview').prop('disabled', false).html($('#preview').data('text'));
			});
	}

	$("#file-operations").on('click', function (e) {
		e.preventDefault();
		$(".file-operations ul").toggle();
	});

	$(document).on('mouseup', function (e) {
		var container = $(".file-operations ul");

		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.hide();
		}
	});

	function directDownloadRequest(files) {
		$('.download-block').find('br').remove();
		$('.download-block').find('span').remove();
		var args = {
			file_ids: files,
		}
		axios.post('/file/download', args).then(function (response) {
			console.log(response.data);
			var url = response.data.url;
			var filename = response.data.filename;
			var link = document.createElement('a');
			link.href = url;
			link.setAttribute('download', filename);
			document.body.appendChild(link);
			link.click();
			link.remove();
			if ($('.download-block').length) {
				indexUnselectAll();
			}
			setTimeout(function () {
				$('#download').prop('disabled', false).html($('#download').data('text'));
			}, 500);
		}).catch(function (error) {
			console.log(error);
			$('.download-block').find('p').append($('<br><span class="text-danger">Request completed with an error, please try again</span>'));
			$('#download').prop('disabled', false).html($('#download').data('text'));
		});
	}
});