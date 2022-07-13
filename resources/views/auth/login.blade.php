@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center">
        
        <div class="col-xl-5 col-lg-6 col-md-8">
            <div class="text-center logo">
                <h3 class="login-logo"><span>JP</span><span class="fo-18">Honda</span></h3>
            </div>
            <div class="card">

                <div class="card-body">
                     <p class="login-heading text-center">Log in to enter the application</p> 
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="phone" type="number" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required autocomplete="phone" placeholder="Enter Phone Number" autofocus>
                                
                                <div class="icon">
                                    <img src="/images/man-user.png" />
                                </div>
                                
                                @error('phone')
                                    <span class="invalid-feedback co-w" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password"  placeholder="Enter Password">
                                <div class="icon">
                                    <img src="/images/lock.png" />
                                </div>
                                @error('password')
                                    <span class="invalid-feedback co-w" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-lg-6 col-md-6 col-5">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label d-inline" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 col-md-6 col-7 d-b text-right">
                                <button type="submit" class="btn btn-primary btn-login">
                                    {{ __('Login') }}
                                </button>

                              
                            </div>
                        </div>

                        <div class="form-group row mb-0 text-center">
                               @if (Route::has('password.request'))
                               <!-- <a class="btn btn-link forgot-password" href="{{route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                 </a> -->
                                 <a class="btn btn-link forgot-password leaveClass" href="#">
                                        {{ __('Forgot Your Password?') }}
                                 </a>
                             @endif
                        </div>
                    </form>
                </div>
            </div>

             {{-- Modal --}}
                            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content" style="margin-top: -200px!important;">
                                  <div class="modal-header">
                                    <h4 class="modal-title" id="exampleModalLongTitle">Forgot Password</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">&times;</span>
                                    </button>
                                  </div>
                                  <div class="modal-body">
                                      <div class="row">
                                        <h5 style="margin-left: 10px;">Contact Your Administrator</h5>
                                      </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            {{-- end modal --}}
        </div>
    </div>
</div>
<script src="/js/jquery.min.js"></script>
<script>
    $(document).ready(function() {
    $(document).on('click','.leaveClass',function(){
      $('#exampleModalCenter').modal("show");
    })

});
</script>
@endsection
