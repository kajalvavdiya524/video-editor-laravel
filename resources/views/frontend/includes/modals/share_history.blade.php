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
                <div class="input-group">
                    <input type="text" id="copyTarget" class="form-control" value="{{url('/')}}">
                    <span id="copyButton" class="input-group-addon btn" title="Click to copy">
                    <i class="fa fa-clipboard" aria-hidden="true"></i>
                    </span>
                    <span class="copied">Link copied to clipboard !</span>
                </div>
            </div>
           
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="submit" class="btn btn-primary" data-dismiss="modal">Share</button>
            </div>
        </div>
    </div>
</div>
@push("after-scripts")
<script>
(function() {
  "use strict";

  function copyToClipboard(elem) {
    var target = elem;

    // select the content
    var currentFocus = document.activeElement;

    target.focus();
    target.setSelectionRange(0, target.value.length);

    // copy the selection
    var succeed;

    try {
      succeed = document.execCommand("copy");
    } catch (e) {
      console.warn(e);

      succeed = false;
    }

    // Restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
      currentFocus.focus();
    }

    if (succeed) {
      $(".copied").animate({ top: -25, opacity: 0.8 }, 1400, function() {
        $(this).css({ top: 0, opacity: 1 });
      });
    }

    return succeed;
  }

  $("#copyButton, #copyTarget").on("click", function() {
    copyToClipboard(document.getElementById("copyTarget"));
  });
})();
</script>

<style>
.input-group {
  margin-top: 30px;
  position: relative;
}

.input-group {
  position: relative;
}

.input-group-addon {
  border: none;
}

.linkname {
  display: none;
}

#copyButton {
    cursor: pointer;
    background-color: #f4f4f4;
    border: 1px solid #ddd5d5;
    border-left: none;
}

#copyTarget{
    z-index: 100;
}

.copied {
  opacity: 1;
  position: absolute;
  left: 14px;
  z-index: 0;
}

@media (min-width: 768px) {
  .copied {
    left: 5px;
  }

  .linkname {
    display: block;
    background: #3b3e45;
    color: #fff;
  }
}
</style>
@endpush