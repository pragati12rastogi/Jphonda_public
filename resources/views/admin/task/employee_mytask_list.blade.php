<table id="admin_table" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                      <th>ID</th>
                      <th>Task</th>                     
                      <th>Assigned BY</th>                     
                      <th>Priority</th>                                                   
                      <th>Task Date</th>                     
                      <th>Status</th>                                       
                      <th>Action</th>                                       
                    </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
                  </table>
                  <div id="myModal_comp_done" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                          
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Add Status</h4>
                                    </div>
                                    <div class="modal-body">
                                       <div class="alert alert-danger" id="js-msg-errors"></div>
                                       <div class="alert alert-success" id="js-msg-successs"></div>
                                        <!-- <form action="javascript:void(0)" id="completion_mytask_form" method="post"> -->
                                          @csrf
                                          <span id="fs_err" style="color:red; display: none;"></span>
                                          <input type="text" name="task_id" id="task_id" hidden>
                                          <div class="row">
                                            <div class="col-md-6 {{ $errors->has('status') ? 'has-error' : ''}}">
                                                <label for="">Status<sup>*</sup></label>
                                                <select name="status" id="status" class="input-css select2 status">
                                                  <option value="">Select Status</option>
                                                  <option value="completed">Completed</option>
                                                  <option value="InProgress">In Progress</option>
                                                  <option value="NotStarted">Not Started</option>
                                                </select>
                                                {!! $errors->first('status', '<p class="help-block">:message</p>') !!}
                                            </div>
                                          </div><br><br>
                                          <div class="row" id="mytask_div">
                                              <div class="col-md-6 {{ $errors->has('comment') ? 'has-error' : ''}}">
                                                <label for="">Comment</label>
                                                <textarea id="comment" name="comment" class="comment input-css" ></textarea>
                                                {!! $errors->first('comment', '<p class="help-block">:message</p>') !!}
                                              </div>
                                          </div><br>
                                          <div class="modal-footer">
                                              <input type="button" id="mytaskbutton" value="Update" class="btn btn-primary">&nbsp;&nbsp;
                                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                        <!-- </form> -->
                                    </div>
                
                                </div>
                            </div>
                          </div>