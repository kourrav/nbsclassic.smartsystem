@extends('admin.layouts.app')
@section('panel')
<div class="row mb-none-30">
  <div class="col-xl-12 col-lg-7 col-md-7 mb-30">
    <div class="padding-top padding-bottom">
        <div class="container">
            <div class="row">
              <div class="card card-body">
                <div class="row mt-3">
                    <div class="col-5">
                        <h6 class="title">@lang('Click on Seat to select or deselect')</h6>
                        @if ($trip->day_off)
                        <span class="fs--14px">
                            @lang('Off Days') :
                            @foreach ($trip->day_off as $item)
                            <span class="badge badge--success">
                                {{ __(showDayOff($item)) }}
                                @if (!$loop->last)
                                ,
                                @endif
                            </span>
                            @endforeach
                        </span>
                        @endif
                        @foreach ($trip->fleetType->deck_seats as $seat)
                        <div class="seat-plan-inner">
                            <div class="single">
                                @php
                                echo $busLayout->getDeckHeader($loop->index);
                                @endphp
                                @php
                                $totalRow = $busLayout->getTotalRow($seat);
                                $lastRowSeat = $busLayout->getLastRowSit($seat);
                                $chr = 'A';
                                $deckIndex = $loop->index + 1;
                                $seatlayout = $busLayout->sitLayouts();
                                $rowItem = $seatlayout->left + $seatlayout->right;
                                @endphp
                                @for($i = 1; $i <= $totalRow; $i++)
                                @php if($lastRowSeat==1 && $i==$totalRow -1) break;
                                $seatNumber=$chr;
                                $chr++;
                                $seats=$busLayout->getSeats($deckIndex,$seatNumber);
                                    @endphp
                                    <div class="seat-wrapper">
                                        @php echo $seats->left; @endphp
                                        @php echo $seats->right; @endphp
                                    </div>
                                    @endfor
                                    @if($lastRowSeat == 1)
                                    @php $seatNumber++ @endphp
                                    <div class="seat-wrapper justify-content-center">
                                        @for ($lsr=1; $lsr <= $rowItem+1; $lsr++) @php echo $busLayout->generateSeats($lsr,$deckIndex,$seatNumber); @endphp
                                            @endfor
                                    </div><!-- single-row end -->
                                    @endif

                                    @if($lastRowSeat > 1)
                                    @php $seatNumber++ @endphp
                                    <div class="seat-wrapper justify-content-center">
                                        @for($l = 1; $l <= $lastRowSeat; $l++) @php echo $busLayout->generateSeats($l,$deckIndex,$seatNumber); @endphp
                                            @endfor
                                    </div><!-- single-row end -->
                                    @endif
                            </div>
                        </div>
                        @endforeach
                        <div class="seat-for-reserved">
                            <div class="seat-condition available-seat">
                                <span class="seat"><span></span></span>
                                <p>@lang('Available Seats')</p>
                            </div>
                            <div class="seat-condition selected-by-you">
                                <span class="seat"><span></span></span>
                                <p>@lang('Selected by You')</p>
                            </div>
                            <div class="seat-condition selected-by-gents">
                                <div class="seat"><span></span></div>
                                <p>@lang('Booked by Gents')</p>
                            </div>
                            <div class="seat-condition selected-by-ladies">
                                <div class="seat"><span></span></div>
                                <p>@lang('Booked by Ladies')</p>
                            </div>
                            <div class="seat-condition selected-by-others">
                                <div class="seat"><span></span></div>
                                <p>@lang('Booked by Others')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-7">
                        <div class="seat-overview-wrapper">
                            <form action="{{ route('admin.booking.tickets.book', $trip->id) }}" method="POST" id="bookingForm" class="row gy-2">
                                @csrf
                                <input type="text" name="price" hidden>
                                <div class="row">
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="date_of_journey" class="form-label">@lang('Journey Date')</label>
                                          <input type="text" id="date_of_journey" class="form--control datepicker1" value="{{ Session::get('date_of_journey') ? Session::get('date_of_journey') : date('m/d/Y') }}" name="date_of_journey">
                                      </div>
                                  </div>
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="pickup_point" class="form-label">@lang('Pickup Point')</label>
                                          <select name="pickup_point" id="pickup_point" class="form--control select2">
                                              <option value="">@lang('Select One')</option>
                                              @foreach($stoppages as $item)
                                              <option value="{{ $item->id }}" @if (Session::get('pickup')==$item->id)
                                                  selected
                                                  @endif>
                                                  {{ __($item->name) }}
                                              </option>
                                              @endforeach
                                          </select>
                                      </div>
                                  </div>
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="dropping_point" class="form-label">@lang('Dropping Point')</label>
                                          <select name="dropping_point" id="dropping_point" class="form--control select2">
                                              <option value="">@lang('Select One')</option>
                                              @foreach($stoppages as $item)
                                              <option value="{{ $item->id }}" @if (Session::get('destination')==$item->id)
                                                  selected
                                                  @endif>
                                                  {{ __($item->name) }}
                                              </option>
                                              @endforeach
                                          </select>
                                      </div>

                                  </div>
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="price" class="form-label">@lang('Price')</label>
                                          <select name="price" id="price" class="form--control select2">
                                              <option value="">@lang('Select price')</option>
                                              <option value ="">Business Price</option>
                                              <option value ="">Economy Price</option>
                                          </select>
                                      </div>
                                  </div>
                                </div>
                                <div class="row seat-select seatselected">
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="child_seat" class="form-label">@lang('Children Seat')</label>
                                          <input type="number" name="child_seat seat" class="form--control child_seat seat" placeholder="@lang('Children Seat')" autocomplete="off" value="0" min="1">
                                      </div>
                                  </div>
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="special_seat" class="form-label">@lang('Special Seat')</label>
                                          <input type="number" name="special_seat seat" class="form--control special_seat seat" placeholder="@lang('Special Seat')" autocomplete="off" value="0" min="1">
                                      </div>
                                  </div>
                                  <div class="col-4">
                                      <div class="form-group">
                                          <label for="adult_seat" class="form-label">@lang('Adult Seat')</label>
                                          <input type="number" name="adult_seat seat" class="form--control adult_seat seat" placeholder="@lang('Adult Seat')" autocomplete="off" value="0" min="1">
                                      </div>
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col-4">
                                      <h6 class="form-label">Type</h6>
                                  </div>
                                  <div class="col-4">
                                      <h6 class="form-label">Price</h6>
                                  </div>
                                  <div class="col-4">
                                      <h6 class="form-label">Total</h6>
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col-4 mt-2"><h6 class="form-label">Type</h6></div>
                                  <div class="col-4 mt-2"><h6 class="form-label">Price</h6></div>
                                  <div class="col-4 mt-2"><h6 class="form-label">Total</h6></div>
                                </div>

                                  <div class="booked-seat-details my-3 d-none">
                                      <label>@lang('Selected Seats')</label>
                                      <div class="list-group seat-details-animate">
                                          <span class="list-group-item d-flex bg--base text-white justify-content-between">@lang('Seat Details')<span>@lang('Price')</span></span>
                                          <div class="selected-seat-details">
                                          </div>
                                      </div>
                                  </div>
                                <input type="hidden" name="seats" class="total_hidden_seats">
                                <div class="col-12 text-center">
                                  <button type="submit" class="book-bus-btn btn--primary">@lang('Process Booking')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
  </div>
</div>

{{-- confirmation modal --}}
<div class="modal fade" id="bookConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> @lang('Confirm Booking')</h5>
                <button type="button" class="w-auto btn--close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
            </div>
            <div class="modal-body">
                <strong class="text-dark">@lang('Are you sure to book these seats?')</strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger w-auto btn--sm px-3" data-bs-dismiss="modal">
                    @lang('Close')
                </button>
                <button type="submit" class="btn btn--success btn--sm w-auto" id="btnBookConfirm">@lang("Confirm")
                </button>
            </div>
        </div>
    </div>
</div>

{{-- alert modal --}}
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"> @lang('Alert Message')</h5>
                <button type="button" class="w-auto btn--close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
            </div>
            <div class="modal-body">
                <strong>
                    <p class="error-message text-danger"></p>
                </strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger w-auto btn--sm px-3" data-bs-dismiss="modal">
                    @lang('Continue')
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('script')
<script>
    (function($) {
        "use strict";

        var date_of_journey = '{{ Session::get('
        date_of_journey ') }}';
        var pickup = '{{ Session::get('
        pickup ') }}';
        var destination = '{{ Session::get('
        destination ') }}';

        if (date_of_journey && pickup && destination) {
            showBookedSeat();
        }

        //reset all seats
        function reset() {
            $('.seat-wrapper .seat').removeClass('selected');
            $('.seat-wrapper .seat').parent().removeClass('seat-condition selected-by-ladies selected-by-gents selected-by-others disabled');
            $('.selected-seat-details').html('');
          }
        //click on seat
        $('.seat-wrapper .seat').on('click', function() {
            if (!$(this).parent().hasClass("disabled"))
            $(this).toggleClass("selected");
            var pickupPoint = $('select[name="pickup_point"]').val();
            var droppingPoing = $('select[name="dropping_point"]').val();
            if (pickupPoint && droppingPoing ) {
                selectSeat(this);
            } else {
                $(this).removeClass('selected');
                notify('error', "@lang('Please select pickup point and dropping point before select any seat')")
            }
          });

        //select and booked seat
        function selectSeat(a) {
           let selectedSeats = $('.seat.selected');
           let  childrenseat='';
            //console.log(selectedSeats+'sge');
            let seatDetails = '';
            let price = $('input[name=price]').val();
            let subtotal = 0;
            let currency = '{{ __($general->cur_text) }}';
            let seats = '';
            if (selectedSeats.length ) {
              if(selectedSeats.length > 6 ){
                $(a).removeClass('selected');
                notify('error', "@lang('Sorry you can not select more than 6 seats at a time')");
              }
              else {
                $('.booked-seat-details').removeClass('d-none');
                  $.each(selectedSeats, function(i, value) {
                      seats += $(value).data('seat') + ',';
                      seatDetails += `<span class="list-group-item d-flex justify-content-between">${$(value).data('seat')} <span>${price} ${currency}</span></span>`;
                    //  console.log(currency);
                      subtotal = subtotal + parseFloat(price);
                    //  console.log(price);
                  });

                  $('input[name=seats]').val(seats);
                  $('.selected-seat-details').html(seatDetails);
                  $('.selected-seat-details').append(`<span class="list-group-item d-flex justify-content-between">@lang('Sub total')<span>${subtotal} ${currency}</span></span>`);
              }
            }
            else
            {
                $('.selected-seat-details').html('');
                $('.booked-seat-details').addClass('d-none');
              }
        }

        //on change date, pickup point and destination point show available seats
        $(document).on('change', 'select[name="pickup_point"], select[name="dropping_point"], select[name="price"], input[name="date_of_journey"]', function(e) {
            showBookedSeat();
        });

        //child,speacial,adult
        $(document).on("change",".child_seat, .special_seat, .adult_seat",function() {
        //  var selectedSeats;
        // var totalSeats = $('.total_hidden_seats').val(aa);
         var sum=0;
         var val1=parseInt($(".child_seat").val());
         var val2=parseInt($(".special_seat").val());
         var val3=parseInt($(".adult_seat").val());
         var sum=parseInt(val1 + val2 + val3);
         console.log(sum);
         //console.log( $('.total_hidden_seats').val(aa));
        //    console.log($(".child_seat").val());
        //   console.log  ($(".special_seat").val());
        //     console.log($(".adult_seat").val());
       // $('.child_seat, .special_seat, .adult_seat').addClass('selected');
       //   if(sum > 6){
       //    notify('error', "@lang('Please enter the passengers count 6 or less than six')")
       //  }
         //  if( sum == totalSeats   ){
         //  notify('error', "@lang('sum is less ')")
         // }
         $('.total_hidden_seats').on('change', function() {
                var totalSeats = $('.total_hidden_seats').val();
           console.log( totalSeats);
             // if (pickupPoint && droppingPoing ) {
             //
             // } else {
             //
             // }
           });
       });

      //booked seat
        function showBookedSeat() {
            reset();
            var date = $('input[name="date_of_journey"]').val();
            var sourceId = $('select[name="pickup_point"]').find("option:selected").val();
            var destinationId = $('select[name="dropping_point"]').find("option:selected").val();
            var price = $('select[name="price"]').find("option:selected").val();
            console.log(price);
            if (sourceId == destinationId && destinationId != '') {
                notify('error',"@lang('Source Point and Destination Point Must Not Be Same')");
                $('select[name="dropping_point"]').val('').select2();
                return false;
            } else if (sourceId != destinationId) {

                var routeId = '{{ $trip->route->id }}';
                var fleetTypeId = '{{ $trip->fleetType->id }}';

                if (sourceId && destinationId) {
                    getprice(routeId, fleetTypeId, sourceId, destinationId, date,price)
                }
            }
        }

        // check price, booked seat etc
        function getprice(routeId, fleetTypeId, sourceId, destinationId, date) {
            var data = {
                "trip_id": '{{ $trip->id }}',
                "vehicle_route_id": routeId,
                "fleet_type_id": fleetTypeId,
                "source_id": sourceId,
                "destination_id": destinationId,
                "date": date,
            }
            $.ajax({
                type: "get",
                url: "{{ route('admin.booking.tickets.get-price') }}",
                data: data,
                success: function(response) {

                    if (response.error) {
                        var modal = $('#alertModal');
                        modal.find('.error-message').text(response.error);
                        modal.modal('show');
                        $('select[name="pickup_point"]').val('');
                        $('select[name="dropping_point"]').val('');
                        $('select[name="price"]').val('');
                    } else {
                        var stoppages = response.stoppages;

                        var reqSource = response.reqSource;
                        var reqDestination = response.reqDestination;

                        reqSource = stoppages.indexOf(reqSource.toString());
                        reqDestination = stoppages.indexOf(reqDestination.toString());

                        if (response.reverse == true) {
                            $.each(response.bookedSeats, function(i, v) {
                                var bookedSource = v.pickup_point; //Booked
                                var bookedDestination = v.dropping_point; //Booked

                                bookedSource = stoppages.indexOf(bookedSource.toString());
                                bookedDestination = stoppages.indexOf(bookedDestination.toString());

                                if (reqDestination >= bookedSource || reqSource <= bookedDestination) {
                                    $.each(v.seats, function(index, val) {
                                        if(v.gender == 1){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().removeClass('seat-condition selected-by-gents disabled');
                                        }
                                         if(v.gender == 2){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().removeClass('seat-condition selected-by-ladies disabled');
                                        }
                                        if(v.gender == 3){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().removeClass('seat-condition selected-by-others disabled');
                                        }
                                    });
                                } else {
                                    $.each(v.seats, function(index, val) {
                                        if(v.gender == 1){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().addClass('seat-condition selected-by-gents disabled');
                                        }
                                        if(v.gender == 2){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().addClass('seat-condition selected-by-ladies disabled');
                                        }
                                        if(v.gender == 3){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().addClass('seat-condition selected-by-others disabled');
                                        }
                                    });
                                }
                            });
                        } else {
                            $.each(response.bookedSeats, function(i, v) {
                                console.log(i, v);
                                var bookedSource = v.pickup_point; //Booked
                                var bookedDestination = v.dropping_point; //Booked
                                var price = v.price;

                                bookedSource = stoppages.indexOf(bookedSource.toString());
                                bookedDestination = stoppages.indexOf(bookedDestination.toString());
                                price = stoppages.indexOf(price.toString());

                                if (reqDestination <= bookedSource || reqSource >= bookedDestination) {
                                    $.each(v.seats, function(index, val) {
                                        if(v.gender == 1){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().removeClass('seat-condition selected-by-gents disabled');
                                        }
                                         if(v.gender == 2){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().removeClass('seat-condition selected-by-ladies disabled');
                                        }
                                        if(v.gender == 3){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().removeClass('seat-condition selected-by-others disabled');
                                        }
                                    });
                                } else {
                                    $.each(v.seats, function(index, val) {
                                        if(v.gender == 1){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().addClass('seat-condition selected-by-gents disabled');
                                        }
                                        if(v.gender == 2){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().addClass('seat-condition selected-by-ladies disabled');
                                        }
                                        if(v.gender == 3){
                                            $(`.seat-wrapper .seat[data-seat="${val}"]`).parent().addClass('seat-condition selected-by-others disabled');
                                        }
                                    });
                                }
                            });
                        }

                        if (response.price.error) {
                            var modal = $('#alertModal');
                            modal.find('.error-message').text(response.price.error);
                            modal.modal('show');
                        } else {
                            $('input[name=price]').val(response.price);
                        }
                    }
                }
            });
        }

        //booking form submit
        $('#bookingForm').on('submit', function(e) {
            e.preventDefault();
            let selectedSeats = $('.seat.selected');
            if (selectedSeats.length > 0) {
                var modal = $('#bookConfirm');
                modal.modal('show');
            } else {
                notify('error', 'Select at least one seat.');
            }
        });

        $('.memberslogin').on('click', function(e){
           // e.preventDefault();
            var modal = $('#MembersLogin');
            modal.modal('show');
        });
        $('.guestlogin').on('click', function(e){
           // e.preventDefault();
            var modal = $('#GuestLogin');
            modal.modal('show');
        });
        /*
        $('#loginsubmit_btn').on('click', function(e){
            e.preventDefault();
            var modal = $('#MembersLogin');
            //modal.modal('hide');
            document.getElementById("shortlogin").submit();
        });
        */
        jQuery(document).on('submit', "#shortlogin", function(e) {
        e.preventDefault();
        var formObj = jQuery(this);
        var formURL = formObj.attr("action");
        if (window.FormData !== undefined) // for HTML5 browsers
        {
            var formData = new FormData(this);
            jQuery.ajax({
                url: formURL,
                type: 'POST',
                data: formData,
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                dataType: 'json',
                processData: false,
                success: function(data, textStatus, jqXHR) {
                    console.log(data);
                    if(data == 1){
                        location.reload();
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                        notify('error', jqXHR.responseJSON.errors.username[0]);

                    }
            });
        } else {
            var iframeId = 'unique' + (new Date().getTime());
            var iframe = $('<iframe src="javascript:false;" name="' + iframeId + '" />');
            iframe.hide();
            formObj.attr('target', iframeId);
            iframe.appendTo('body');
            iframe.load(function(e) {
                var doc = getDoc(iframe[0]);
                var docRoot = doc.body ? doc.body : doc.documentElement;
                var data = docRoot.innerHTML;
            });
        }
        });

        //guestsubmit_btn
        jQuery(document).on('submit', "#guestsubmit_btn", function(e) {
        e.preventDefault();
        var formObj = jQuery(this);
        var formURL = formObj.attr("action");
        if (window.FormData !== undefined) // for HTML5 browsers
        {
            var formData = new FormData(this);
            jQuery.ajax({
                url: formURL,
                type: 'POST',
                data: formData,
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                dataType: 'json',
                processData: false,
                success: function(data, textStatus, jqXHR) {
                    console.log(data);
                    if(data == 1){
                        location.reload();
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR);
                        notify('error', jqXHR.responseJSON.errors.username[0]);

                    }
            });
        } else {
            var iframeId = 'unique' + (new Date().getTime());
            var iframe = $('<iframe src="javascript:false;" name="' + iframeId + '" />');
            iframe.hide();
            formObj.attr('target', iframeId);
            iframe.appendTo('body');
            iframe.load(function(e) {
                var doc = getDoc(iframe[0]);
                var docRoot = doc.body ? doc.body : doc.documentElement;
                var data = docRoot.innerHTML;
            });
        }
        });

        //confirmation modal
        $(document).on('click', '#btnBookConfirm', function(e) {
            var modal = $('#bookConfirm');
            modal.modal('hide');
            document.getElementById("bookingForm").submit();
        });

        $(document).ready(function () {
            $(".select2").select2();
            $(".datepicker1").datepicker();
        });

    })(jQuery);
</script>
@endpush

@push('style')
    <style>
        .hover-input-popup {
            position: relative;
        }
        .hover-input-popup:hover .input-popup {
            opacity: 1;
            visibility: visible;
        }
        .input-popup {
            position: absolute;
            bottom: 130%;
            left: 50%;
            width: 280px;
            background-color: #1a1a1a;
            color: #fff;
            padding: 20px;
            border-radius: 5px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            -ms-border-radius: 5px;
            -o-border-radius: 5px;
            -webkit-transform: translateX(-50%);
            -ms-transform: translateX(-50%);
            transform: translateX(-50%);
            opacity: 0;
            visibility: hidden;
            -webkit-transition: all 0.3s;
            -o-transition: all 0.3s;
            transition: all 0.3s;
        }
        .input-popup::after {
            position: absolute;
            content: '';
            bottom: -19px;
            left: 50%;
            margin-left: -5px;
            border-width: 10px 10px 10px 10px;
            border-style: solid;
            border-color: transparent transparent #1a1a1a transparent;
            -webkit-transform: rotate(180deg);
            -ms-transform: rotate(180deg);
            transform: rotate(180deg);
        }
        .input-popup p {
            padding-left: 20px;
            position: relative;
        }
        .input-popup p::before {
            position: absolute;
            content: '';
            font-family: 'Line Awesome Free';
            font-weight: 900;
            left: 0;
            top: 4px;
            line-height: 1;
            font-size: 18px;
        }
        .input-popup p.error {
            text-decoration: line-through;
        }
        .input-popup p.error::before {
            content: "\f057";
            color: #ea5455;
        }
        .input-popup p.success::before {
            content: "\f058";
            color: #28c76f;
        }
    </style>
@endpush

@push('script')
    <script>
        "use strict";
        function submitUserForm() {
            var response = grecaptcha.getResponse();
            if (response.length == 0) {
                document.getElementById('g-recaptcha-error').innerHTML = '<span class="text-danger">@lang("Captcha field is required.")</span>';
                return false;
            }
            return true;
        }
        (function ($) {
            @if($mobile_code)
            $(`option[data-code={{ $mobile_code }}]`).attr('selected','');
            @endif

            $('select[name=country]').change(function(){
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
            });
            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+'+$('select[name=country] :selected').data('mobile_code'));
            @if($general->secure_password)
                $('input[name=password]').on('input',function(){
                    secure_password($(this));
                });
            @endif

            $('.checkUser').on('focusout',function(e){
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('.mobile-code').text().substr(1)}${value}`;
                    var data = {mobile:mobile,_token:token}
                }
                if ($(this).attr('name') == 'email') {
                    var data = {email:value,_token:token}
                }
                if ($(this).attr('name') == 'username') {
                    var data = {username:value,_token:token}
                }
                $.post(url,data,function(response) {
                    if (response['data'] && response['type'] == 'email') {
                    $('#existModalCenter').modal('show');
                    }else if(response['data'] != null){
                    $(`.${response['type']}Exist`).text(`${response['type']} already exist`);
                    }else{
                    $(`.${response['type']}Exist`).text('');
                    }
                });
            });

        })(jQuery);

    </script>
@endpush
