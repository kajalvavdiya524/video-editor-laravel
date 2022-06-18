require('../bootstrap');
require('ekko-lightbox')
require('paginationjs');
var jQueryBridget = require('jquery-bridget');
var Masonry = require('masonry-layout');
var pageSize = 18;
var searchKeyword = '';
var image_type = "product";
jQueryBridget('masonry', Masonry, $);

var indexCheckedFiles = [];
var selectedProducts = [];
var product_idx = 0;
var selected_files = [];

function image_pagination(type = "product") {
	var dataSource;
	if (type == "product") {
		dataSource = `/file/file_list?searchKey=${searchKeyword}`;
	} else {
		dataSource = `/file/background_list?searchKey=${searchKeyword}`;
	}
	$('.image-grid-pagination').pagination({
		dataSource: dataSource,
		locator: 'items',
		totalNumberLocator: function (response) {
			return response.totalCount;
		},
		pageSize: pageSize,
		ajax: {
			beforeSend: function () {
				$(".grid").html('Loading data from database ...');
			}
		},
		callback: function (data, pagination) {
			// template method of yourself
			var html = template(data);
			$(".grid").html(html);
			console.log(searchKeyword);
			// console.log('data', data);
			console.log('pagination', pagination);
		}
	});
}

function template(data) {
	var html = "";
	data.forEach(element => {
		if (indexCheckedFiles.includes(element.id))
			html += "<div class='grid-item selected'>";
		else
			html += "<div class='grid-item'>";

		html += "<input type='hidden' value='" + element.id + "'/>";
		html += "<input type='checkbox' class='select-check' />";
		if (element.status == 'new')
			html += "<span class='budge'>New</span>";
		html += "<img src='" + element.url + "' loading='lazy'/>";
		html += "<p>" + element.name + "</p>";
		html += "</div>";
	});
	return html;
}

$(document).ready(function () {

	localStorage.removeItem("selected_files");

	image_pagination();

	$('.grid').masonry({
		itemSelector: '.grid-item',
		columnWidth: 220
	});

	$(document).on('click', '.grid-item', function (e) {
		var id = Number($(this).find('input').val());
		var name = $(this).find('p').text().trim().split('.')[0];
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$(this).children('.select-check').prop('checked', false);
			indexCheckedFiles = _.pull(indexCheckedFiles, id);
			selected_files = _.pull(selected_files, name);
		} else {
			$(this).addClass('selected');
			$(this).children('.select-check').prop('checked', true);
			indexCheckedFiles.push(id);
			selected_files.push(name);
		}
		updateDownloadBlock();
	});

	$("#product-image-tab").on('click', function (e) {
		e.preventDefault();
		$(this).css('pointer-events', 'none');
		$("#background-image-tab").css('pointer-events', 'auto');
		$(".button-group").show();
		image_type = "product";
		image_pagination(image_type);
	});
	
	$("#background-image-tab").on('click', function (e) {
		e.preventDefault();
		$(this).css('pointer-events', 'auto');
		$("#background-image-tab").css('pointer-events', 'none');
		$(".button-group").hide();
		image_type = "background";
		image_pagination(image_type);
	});

	$('#reindex').on('click', function (e) {
		if ($('.grid-item').length == 0) return;
		e.preventDefault();
		$(this).prop('disabled', true);
		$(this).html($(this).data('loading-text'));
		var form = $('<form method="post" action="/file/reindex" style="display:none"></form>');
		form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
		$(document.body).append(form);
		form.submit();
	});

	$('#export').on('click', function (e) {
		if ($('.grid-item').length == 0) return;
		var form = $('<form method="post" action="/file/export" style="display:none"></form>');
		form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
		$(document.body).append(form);
		form.submit();
	});

	$('#create-ads').on('click', function (e) {
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

	$('#generate-thumbnail').on('click', function (e) {
		if ($('.grid-item').length == 0) return;
		e.preventDefault();
		$(this).prop('disabled', true);
		$(this).html($(this).data('loading-text'));
		var form = $('<form method="post" action="/file/generate_thumbnail" style="display:none"></form>');
		form.append($('<input type="hidden" name="_token" id="csrf-token" value="' + $('meta[name="csrf-token"]').attr('content') + '" />'));
		$(document.body).append(form);
		form.submit();
	});

	$('#re-generate-thumbnail').on('click', function (e) {
		if ($('.grid-item').length == 0) return;
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

	function showProductView(file) {
		$('#productViewModal #product_name').html(file['product_name']);
		$('#productViewModal #brand').html(file['brand']);
		$('#productViewModal #primary_filename').html(file['name']);
		$('#productViewModal #category').html("");	// to do
		$('#productViewModal #tags').html("");	// to do
		$('#productViewModal #asin').html(file['asin']);
		$('#productViewModal #upc').html(file['upc']);
		$('#productViewModal #gtin').html("");	// to do
		$('#productViewModal #width').html(file['width']);
		$('#productViewModal #height').html(file['height']);
		$('#productViewModal #depth').html(file['depth']);
		$('#productViewModal #full-size-image').attr('src', file['url']);
		$('#productViewModal #full-size-image').data('name', file['name']);
		$('#productViewModal #full-size-image').data('type', "");
		$('#productViewModal #full-size-image').data('company_id', file['company_id']);
		$('#productViewModal #full-size-image').data('path', file['path']);
		$('#productViewModal #full-size-image_nf').attr('src', file['nf_thumbnail_large']);
		$('#productViewModal #full-size-image_ingredient').attr('src', file['ingredient_thumbnail_large']);

		if (file['thumbnail'] != '') {
			$('#productViewModal #primary-thumbnail').parent().show();
			$('#productViewModal #primary-thumbnail').attr('src', file['thumbnail']);
		} else {
			$('#productViewModal #primary-thumbnail').parent().hide();
		}
		if (file['nf_thumbnail'] != '') {
			$('#productViewModal #nf-thumbnail').parent().show();
			$('#productViewModal #nf-thumbnail').attr('src', file['nf_thumbnail']);
		} else {
			$('#productViewModal #nf-thumbnail').parent().hide();
		}
		if (file['ingredient_thumbnail'] != '') {
			$('#productViewModal #ingredient-thumbnail').parent().show();
			$('#productViewModal #ingredient-thumbnail').attr('src', file['ingredient_thumbnail']);
		} else {
			$('#productViewModal #ingredient-thumbnail').parent().hide();
		}
		$('#productViewModal .modal-title').text(file['product_name']);

		$('.thumbnail-images li').removeClass('selected');
		$('.thumbnail-images li').first().addClass('selected');
		$('.single-image').hide();
		$('.detail-view').show();
		$('#productViewModal').modal();
		$('#preview').prop('disabled', false).html($('#preview').data('text'));

		// crop image tool
		$('.croppr-container').remove();
		$('#full-size-image').show();
		$('.image-crop .button-group').hide();
		$('#image-crop-button').show();
	}

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
				selectedProducts = data.files;
				product_idx = 0;
				$('#product-images').empty();
				if (selectedProducts.length == 1) {
					$('.prev_next_group').hide();
				} else {
					$('.prev_next_group').show();
				}

				// $('#edit-product-data').show();
				// $('#close-product-data').hide();
				// $('#save-product-data').hide();
				showProductView(selectedProducts[product_idx]);
			})
			.catch(function (response) {
				console.error(response);
				$('#preview').prop('disabled', false).html($('#preview').data('text'));
			});
	}

	$('.thumbnail-images li').on('click', function (e) {
		var file = selectedProducts[product_idx];
		$('.thumbnail-images li').removeClass('selected');
		$(this).addClass('selected');
		var index = $('.thumbnail-images li').index(this);
		if (index == 0) {
			$('#productViewModal #full-size-image').attr('src', file['url']);
			$('#productViewModal #full-size-image').data('name', file['name']);
			$('#productViewModal #full-size-image').data('type', "");
			$('#productViewModal #full-size-image').data('company_id', file['company_id']);
			$('#productViewModal #full-size-image').data('path', file['path']);
		} else if (index == 1) {
			$('#productViewModal #full-size-image').attr('src', file['nf_thumbnail_large']);
			$('#productViewModal #full-size-image').data('name', file['name']);
			$('#productViewModal #full-size-image').data('type', "Nutrition_Facts_Images");
			$('#productViewModal #full-size-image').data('company_id', file['company_id']);
			$('#productViewModal #full-size-image').data('path', `files/${file['company_id']}/Ingredient_Images/${file['name']}`);
		} else if (index == 2) {
			$('#productViewModal #full-size-image').attr('src', file['ingredient_thumbnail_large']);
			$('#productViewModal #full-size-image').data('type', "Ingredient_Images");
			$('#productViewModal #full-size-image').data('name', file['name']);
			$('#productViewModal #full-size-image').data('company_id', file['company_id']);
			$('#productViewModal #full-size-image').data('path', `files/${file['company_id']}/Ingredient_Images/${file['name']}`);
		}
	});

	$('#productViewModal #full-size-image').on('click', function (e) {
		var src = $('#productViewModal #full-size-image').attr('src');
		$('#productViewModal .single-image #single').attr('src', src);
		$('.single-image').show();
		$('.detail-view').hide();
	});

	$('#back-to-detail').on('click', function (e) {
		e.preventDefault();
		$('.single-image').hide();
		$('.detail-view').show();
	});

	$('#next_product').on('click', function (e) {
		e.preventDefault();
		if (product_idx == selectedProducts.length - 1) return;
		product_idx++;
		showProductView(selectedProducts[product_idx]);
		if (product_idx == selectedProducts.length - 1) {
			$(this).removeAttr('href');
		} 
		$('#prev_product').attr('href', '#');
	});

	$('#prev_product').on('click', function (e) {
		e.preventDefault();
		if (product_idx == 0) return;
		product_idx--;
		showProductView(selectedProducts[product_idx]);
		if (product_idx == 0) {
			$(this).removeAttr('href');
		} 
		$('#next_product').attr('href', '#');
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
	}

	$('#global-download-unselect').on('click', function (e) {
		indexUnselectAll();
		updateDownloadBlock();
	});

	function indexUnselectAll() {
		indexCheckedFiles = [];
		$('.grid-item').removeClass('selected');
	}
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
			console.error(error);
			$('.download-block').find('p').append($('<br><span class="text-danger">Request completed with an error, please try again</span>'));
			$('#download').prop('disabled', false).html($('#download').data('text'));
		});
	}

	$('#index-search-input').on('change', function (e) {//change removed
		searchKeyword = $(this).val();
		image_pagination(image_type);
	});

	function switch_td_to_editbox(target, id, type) {
		var val = $(target).text();
		$(target).html('<input type="' + type + '" id="' + id + '" value="' + val + '" />');
	}

	$('#edit-product-data').on('click', function (e) {
		switch_td_to_editbox('.product-info #product_name', 'editbox-product-name', 'text');
		switch_td_to_editbox('.product-info #brand', 'editbox-brand', 'text');
		switch_td_to_editbox('.product-info #category', 'editbox-category', 'text');
		switch_td_to_editbox('.product-info #tags', 'editbox-tags', 'text');
		switch_td_to_editbox('.product-info #primary_filename', 'editbox-primary-filename', 'text');
		switch_td_to_editbox('.product-info #asin', 'editbox-asin', 'text');
		switch_td_to_editbox('.product-info #upc', 'editbox-upc', 'number');
		switch_td_to_editbox('.product-info #gtin', 'editbox-gtin', 'number');
		switch_td_to_editbox('.product-info #width', 'editbox-width', 'number');
		switch_td_to_editbox('.product-info #height', 'editbox-height', 'number');
		switch_td_to_editbox('.product-info #depth', 'editbox-depth', 'number');

		$(this).hide();
		$('.prev_next_group').hide();
		$('#close-product-data').show();
		$('#save-product-data').show();
	});
});