                         @if($data1->count()>0)  
                    
                        <div class="row  pt-20">
                              @foreach($data1 as $row)
                            <div class="store_div">
                                <div class="contact-info-wrapper">
                                    <div class="communication-info" style="margin-bottom:50px;">
                                        <div class="single-communication"  id="store_data" >
                                            <table class="table-content table-responsive store_table">
                                                <tr>
                                                    <th width="10%">Name</th>
                                                    <td width="10%">{{ $row->name }}</td>
                                                     <th width="10%">Store Type</th>
                                                    <td width="10%">{{ $row->store_type }}</td>
                                                </tr>
                                                <tr>
                                                    <th width="10%">City</th>
                                                    <td width="10%">{{ $row->city }}</td>
                                                     <th width="10%">Mobile</th>
                                                    <td width="10%">{{ $row->mobile }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="single-communication" id="store_data" >
                                            <div class="communication-text">
                                                <h4>Address</h4>
                                            </div>
                                            <div class="communication-text">
                                                <p style="margin-left: 75px;">{{ $row->address }}</p>
                                            </div>
                                        </div>
                                        <div class="map">
                                         <?php 

                                        $address = $row->address.','.$row->city;
                                         echo '<iframe  hight="300" width="400" frameborder="0" src="https://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=' . str_replace(",", "", str_replace(" ", "+", $address)) . '&z=14&output=embed"></iframe>';

                                 ?>

                                    </div>
                                    </div>
                                    
                                </div>
                            </div>
                               @endforeach
                                @else
                            <span>No data available </span>
                            @endif
                        </div>

<div class="text-center">                              
    {!! $data1->links() !!}
</div>
