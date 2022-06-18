<div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Share...</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(Route::is('frontend.banner.*'))
                <div class="form-group" id="select-choice">
                    <p>Project needs to be saved before sharing:</p>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="save_draft" name="share_ads" class="custom-control-input" value="save" checked>
                        <label class="custom-control-label" for="save_draft">Save Draft</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="publish_to_team" name="share_ads" class="custom-control-input" value="publish">
                        <label class="custom-control-label" for="publish_to_team">{{ $logged_in_user->isTeamMember() ? "Publish Project" : "Save Project" }}</label>
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <label>Please enter emails to share with: <span style="color: grey">(Separate multiple emails with commas or spaces.)</span></label>
                    <input type="text" name="share_email" id="share_email" class="form-control">
                </div>
                @if(!Route::is('frontend.banner.*'))
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="share_subject" id="share_subject" class="form-control">
                </div>
                <div class="form-group">
                    <label>Body</label>
                    <textarea rows="4" name="share_body" id="share_body" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" id="request_approval" name="request_approval" class="custom-control-input">
                        <label class="custom-control-label" for="request_approval">Request Approval</label>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Share</button>
            </div>
        </div>
    </div>
</div>