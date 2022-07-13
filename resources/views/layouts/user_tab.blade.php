<ul class="nav user-tab-nav nav1 nav-pills">
    <li class="nav-item">
      <a class="nav-link" href="{{url('/admin/hr/user/update/'.$id) }}" style="background-color: {{ Request::segment(4)=='update' ? '#87CEFA' : ''}}">User Details</a>
    </li>
    <li class="nav-item">
      <a class="nav-link"  href="{{url('/admin/hr/user/otherdocumentupdate/'.$id) }}" style="background-color: {{ Request::segment(4)=='otherdocumentupdate' ? '#87CEFA' : ''}}">Other Details</a>
    </li>
    <li class="nav-item">
      <a class="nav-link"  href="{{url('/admin/hr/user/documentupdate/'.$id) }}" style="background-color: {{ Request::segment(4)=='documentupdate' ? '#87CEFA' : ''}}">Bank/PF Details</a>
    </li>
    <li class="nav-item">
      <a class="nav-link"  href="{{url('/admin/payroll/user/salaryupdate/'.$id) }}" style="background-color: {{ Request::segment(4)=='salaryupdate' ? '#87CEFA' : ''}}">Salary  Details</a>
    </li>
</ul>
<br>