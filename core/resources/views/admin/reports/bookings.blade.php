@extends('admin.layouts.app')
@section('panel')
<div class="">
        <div class="card" >

            <div class="card-body">

                <div class="">

                    <form action="{{route('admin.report.bookings')}}" class="form-inline">

                        <div class="col-md-4 col-lg-4">

                            <div class="form-group">
                                <label for="report_date">Start Date </label>
                                

                                <input type="text" name="start_date" id="start_date" class="form-control" placeholder="@lang('Start Date')" autocomplete="off" value="{{ request()->start_date }}" required>

                            </div>

                        </div>
                        <div class="col-md-4 col-lg-4">

                            <div class="form-group">
                                <label for="report_date">End Date </label>
                                

                                <input type="text" name="end_date" id="end_date" class="form-control" placeholder="@lang('End Date')" autocomplete="off" value="{{ request()->end_date }}" required>

                            </div>

                        </div>

                        <div class="col-md-6 col-lg-4">

                            <div class="form-group">

                                <button class="btn btn-primary">@lang('Search Reports')</button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>
</div>

<div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Trip Date')</th>
                                <th>@lang('Title')</th>
                                <th>@lang('Seats Capacity')</th>
                                <th>@lang('Bookings')</th>
                                <th>@lang('Booked By')</th>
                                
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($data as $d)
                            <tr>
                                <td data-label="@lang('Trip Date')">
                                    <span class="font-weight-bold">{{$d['trip_date']}}</span>
                                </td>


                                <td data-label="@lang('Title')">
                                    {{ $d['trip_title'] }}
                                </td>
                                <td data-label="@lang('Seats Capacity')">
                                    @php
                                    $ts = $d['total_seats'];
                                    @endphp
                                    {{ $ts[0] }}
                                </td>
                                <td data-label="@lang('Bookings')">
                                    {{ $d['bookings'] }}
                                </td>
                                <td data-label="@lang('Booked By')">
                                    {{ $d['booked_by'] }}
                                </td>
                                
                            </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                <div class="card-footer py-4">
               
                </div>
            </div>
        </div>


    </div>
@endsection
