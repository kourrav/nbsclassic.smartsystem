@extends('admin.layouts.app')
@section('panel')
@php
$counters = App\Models\Counter::get();
@endphp
<div class="row mb-none-30">
  <div class="col-xl-12 col-lg-7 col-md-7 mb-30">
    <div class="ticket-search-bar bg_imgd padding-top">
      <div class="container">
        <div class="bus-search-header">
          <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.booking.tickets.search') }}" class="ticket-form ticket-form-two row g-3 justify-content-center">
                        <div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <i class="las la-location-arrow"></i>
                                <select name="pickup" class="form--control select2" required>
                                    <option value="">@lang('Pickup Point')</option>
                                    @foreach ($counters as $counter)
                                    <option value="{{ $counter->id }}" @if(request()->pickup == $counter->id) selected @endif>{{ __($counter->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <i class="las la-map-marker"></i>
                                <select name="destination" class="form--control select2" required>
                                    <option value="">@lang('Dropping Point')</option>
                                    @foreach ($counters as $counter)
                                    <option value="{{ $counter->id }}" @if(request()->destination == $counter->id) selected @endif>{{ __($counter->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="form-group">
                                <i class="las la-calendar-check"></i>
                                <input type="text" name="date_of_journey" class="form--control datepicker1" placeholder="@lang('Date of Journey')" autocomplete="off" value="{{ request()->date_of_journey }}">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <button class="btn btn--primary">@lang('Find Tickets')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Ticket Search Starts -->


<!-- Ticket Section Starts Here -->
<section class="ticket-section padding-bottom section-bg">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-12">
                <div class="ticket-wrapper">
                  @if (!(empty($trips)))
                    @forelse ($trips as $trip)
                    @php
                    $start = Carbon\Carbon::parse($trip->schedule->start_from);
                    $end = Carbon\Carbon::parse($trip->schedule->end_at);
                    $diff = $start->diff($end);
                    $ticket = App\Models\TicketPrice::where('fleet_type_id', $trip->fleetType->id)->where('vehicle_route_id', $trip->route->id)->first();
                    @endphp
                    <div class="ticket-item">
                        <div class="ticket-item-inner">
                            <h5 class="bus-name">{{ __($trip->title) }}</h5>
                            <span class="bus-info">@lang('Seat Layout - ') {{ __($trip->fleetType->seat_layout) }}</span>
                            <span class="ratting"><i class="las la-bus"></i>{{ __($trip->fleetType->name) }}</span>
                        </div>
                        <div class="ticket-item-inner travel-time">
                            <div class="bus-time">
                                <p class="time">{{ showDateTime($trip->schedule->start_from, 'h:i A') }}</p>
                                <p class="place">{{ __($trip->startFrom->name) }}</p>
                            </div>
                            <div class=" bus-time">
                                <i class="las la-arrow-right"></i>
                                <p>{{ $diff->format('%H:%I min') }}</p>
                            </div>
                            <div class=" bus-time">
                                <p class="time">{{ showDateTime($trip->schedule->end_at, 'h:i A') }}</p>
                                <p class="place">{{ __($trip->endTo->name) }}</p>
                            </div>
                        </div>
                        <div class="ticket-item-inner book-ticket">
                            <p class="rent mb-0">{{ __($general->cur_sym) }}{{ showAmount($ticket->price) }}</p>
                            @if($trip->day_off)
                            <div class="seats-left mt-2 mb-3 fs--14px">
                                @lang('Off Days'): <div class="d-inline-flex flex-wrap" style="gap:5px">
                                    @foreach ($trip->day_off as $item)
                                    <span class="badge badge--primary">{{ __(showDayOff($item)) }}</span>
                                    @endforeach
                                </div>
                                @else
                                @lang('Every day available')
                                @endif</div>
                            <a class="btn btn--base get_basic_tc_details" data-trip-title="{{slug($trip->title)}}" data-trip-id="{{$trip->id}}" href="javascript:void(0)">@lang('Details')</a>
                        </div>
                        @if ($trip->fleetType->facilities)
                        <div class="ticket-item-footer">
                            <div class="d-flex content-justify-center">
                                <span>
                                    <strong>@lang('Facilities - ')</strong>
                                    @foreach ($trip->fleetType->facilities as $item)
                                    <span class="facilities">{{ __($item) }}</span>
                                    @endforeach
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="ticket-item">
                        <h5>{{ __($emptyMessage) }}</h5>
                    </div>
                    @endforelse
                    @if ( $trips->hasPages())
                    {{ paginateLinks($trips) }}
                    @endif
                  @else
                  <div class="ticket-item">
                      <h5>{{ __($emptyMessage) }}</h5>
                  </div>
                  @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('script')
<script>
    (function($) {
        "use strict";
        $('.search').on('change', function() {
            $('#filterForm').submit();
        });

        $('.reset-button').on('click', function() {
            $('.search').attr('checked', false);
            $('#filterForm').submit();
        })
        $(document).ready(function () {
            $(".select2").select2();
            $(".datepicker1").datepicker();
        });
    })(jQuery)
</script>
@endpush
