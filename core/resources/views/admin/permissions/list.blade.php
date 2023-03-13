@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Agent')</th>
                                <th>@lang('Email-Phone')</th>
                                <th>@lang('Comission') %</th>
                                <th>@lang('Country')</th>
                                <th>@lang('Joined At')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($agents as $agent)
                            <tr>
                                <td data-label="@lang('Agent')">
                                    <span class="font-weight-bold">{{$agent->fullname}}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.agents.detail', $agent->id) }}"><span>@</span>{{ $agent->username }}</a>
                                    </span>
                                </td>
                                <td data-label="@lang('Email-Phone')">
                                    {{ $agent->email }}<br>{{ $agent->mobile }}
                                </td>
                                <td data-label="@lang('Commission')">
                                    {{ $agent->commision ? $agent->commision : 0 }}
                                </td>
                                <td data-label="@lang('Country')">
                                    <span class="font-weight-bold" data-toggle="tooltip" data-original-title="{{ @$agent->address->country }}">{{ $agent->country_code }}</span>
                                </td>

                                <td data-label="@lang('Joined At')">
                                    {{ showDateTime($agent->created_at) }} <br> {{ diffForHumans($agent->created_at) }}
                                </td>

                                <td data-label="@lang('Action')">
                                    <a href="{{ route('admin.agents.detail', $agent->id) }}" class="icon-btn" data-toggle="tooltip" title="" data-original-title="@lang('Details')">
                                        <i class="las la-desktop text--shadow"></i>
                                    </a>
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
                    {{ paginateLinks($agents) }}
                </div>
            </div>
        </div>


    </div>
@endsection



@push('breadcrumb-plugins')
<a href="{{ route('admin.permissions.create')}}" class="btn btn--primary box--shadow1 addBtn"><i class="fa fa-fw fa-plus"></i>@lang('Add New')</a>
    <form action="{{ route('admin.permissions.search', $scope ?? str_replace('admin.permissions.', '', request()->route()->getName())) }}" method="GET" class="form-inline float-sm-right bg--white mb-2 ml-0 ml-xl-2 ml-lg-0">
        <div class="input-group has_append">
            <input type="text" name="search" class="form-control" placeholder="@lang('Username or email')" value="{{ $search ?? '' }}">
            <div class="input-group-append">
                <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </form>
@endpush
