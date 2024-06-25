<div class="form-group col-12">
    <label class="fw-bold mb-1">Title</label>
    {{ html()->text('cf_item[title][]', empty($cf_item) ? '' : $cf_item->title)->class('form-control') }}
</div>
<div class="form-group col-12">
    <label class="fw-bold mb-1">Type</label>
    {{ html()->select('cf_item[type][]', ['profile' => 'Profile', 'letter' => 'Letter'], empty($cf_item) ? null : $cf_item->type)->class('form-control') }}
</div>
<div class="form-group col-12">
    <label class="fw-bold mb-1">Text <small>Use <a href="https://guides.github.com/features/mastering-markdown/" target="_blank">markdown</a> to style the text</small></label>
    {{ html()->textarea('cf_item[text][]', empty($cf_item) ? '' : $cf_item->text)->class('form-control') }}
</div>