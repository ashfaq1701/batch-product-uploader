<div class="container-fluid">
  <div class="alert alert-success" id="batch-post-alert-success" style="display: none">

  </div>
  <div class="alert alert-danger" id="batch-post-alert-success" style="display: none">

  </div>
  <h1>Batch Post Upload</h1>
  <div class="jumbotron">
    <div class="row">
	     <input type="hidden" id="attachment-id" name="attachment-id">
       <div class="col-md-12">
         <div class="form-group">
           <label class="control-label" for="postzip">Post zip file</label>
	         <input type="file" id="postzip">
         </div>
       </div>
       <div class="col-md-12 padding-btm">
         <label class="radio-inline"><input type="radio" name="category" value="fb-post" checked="checked">Facebook Post</label>
         <label class="radio-inline"><input type="radio" name="category" value="tweet">Tweet</label>
         <label class="radio-inline"><input type="radio" name="category" value="site-post">Site Post</label>
       </div>
       <div class="col-md-12">
	        <button id="postzip-submit" class="btn btn success" disabled>Upload Now</button>
       </div>
    </div>
  </div>
</div>
