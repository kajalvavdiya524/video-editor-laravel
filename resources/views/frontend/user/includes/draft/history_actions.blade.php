<x-utils.link
    :text="__('Download')"
    class="btn btn-primary btn-sm"
    :href="route('frontend.history.download', $history)"
    icon="fas fa-download" />
    
<x-utils.link
    :text="__('Share')"
    class="btn btn-success btn-sm share-action"
    data-subject="{{ $history->name . ' Banner'  }}"
    data-body="{{ 'Here is the banner. ' . siteUrl() . '/share?file=' . $history->url }}"
    data-share-link="{{ route('frontend.history.share_history', $history->sharelink()) }}"
    icon="fas fa-share-alt" />

<x-utils.link
    :text="__('Publish')"
    class="btn btn-dark btn-sm"
    :href="route('frontend.history.publish', $history)"
    icon="fas fa-share-alt" />
 <!-- <x-utils.edit-button :href="route('frontend.history.edit', $history)" />-->
 <button type="button" class="btn btn-primary btn-sm btn-history-edit" data-history-id="{{$history->id}}" data-customer-id="{{$history->customer_id()}}">
    <i class="fas fa-pencil-alt"></i> Edit
</button> 
<x-utils.delete-button :href="route('frontend.history.destroy', $history)" />
<!-- <button type="button" class="btn btn-danger btn-sm btn-history-delete" data-history-id="{{$history->id}}">
    <i class="fas fa-trash"></i> Delete
</button> -->
<input type="hidden" id="history-id" value="{{$history->id}}" />
<input type="hidden" id="history-name" value="{{$history->name}}" />