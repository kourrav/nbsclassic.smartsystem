@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card b-radius--10 ">
                <div class="card-body">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                  <th>@lang('ID')</th>
                                  <th>@lang('Name')</th>
                                  <th>@lang('Description')</th>
                                  <th>@lang('Permissions')</th>
                                  <th>@lang('Status')</th>
                                  <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($userstype as $item)
                                  <tr>
                                    <td data-label="@lang('ID')">
                                        {{ __($item->id) }}
                                    </td>
                                    <td data-label="@lang('Name')">
                                        {{ __($item->name) }}
                                    </td>
                                    <td data-label="@lang('Description')">
                                        {{ __($item->description) }}
                                    </td>
                                    <td data-label="@lang('Permissions')">
                                       <?php
                                        if($item->role_permission != ""){
                                          $arrayexpoode = explode(',', $item->role_permission);
                                          echo "<ul>";
                                          foreach($arrayexpoode as $key=>$lu)
                                          {
                                            $name = str_replace('_', ' ', $lu);
                                            echo "<li>".ucwords($name)."</li>";
                                          }
                                          echo "</ul>";
                                        }
                                        ?>
                                    </td>
                                    <td data-label="@lang('Status')">
                                        @if($item->status == 1)
                                        <span class="text--small badge font-weight-normal badge--success">@lang('Active')</span>
                                        @else
                                        <span class="text--small badge font-weight-normal badge--warning">@lang('Disabled')</span>
                                        @endif
                                    </td>
                                    <td data-label="@lang('Action')">
                                        <button type="button" class="icon-btn ml-1 editBtn"
                                                data-toggle="modal" data-target="#editModal"
                                                data-category="{{ $item }}"
                                                data-action="{{ route('admin.roles.update', $item->id) }}"
                                                data-original-title="@lang('Update')">
                                            <i class="la la-pen"></i>
                                        </button>
                                        @if ($item->status != 1)
                                            <button type="button"
                                            class="icon-btn btn--success ml-1 activeBtn"
                                            data-toggle="modal" data-target="#activeModal"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"
                                            data-original-title="@lang('Active')">
                                            <i class="la la-eye"></i>
                                        </button>
                                        @else
                                            <button type="button"
                                                class="icon-btn btn--danger ml-1 disableBtn"
                                                data-toggle="modal" data-target="#disableModal"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-original-title="@lang('Disable')">
                                                <i class="la la-eye-slash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer py-4">
                    {{ paginateLinks($userstype) }}
                </div>
            </div>
        </div>
    </div>


    {{-- Add METHOD MODAL --}}
    <div id="addModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Add Roles')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.roles.store')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-control-label font-weight-bold"> @lang('Name')</label>
                            <input type="text" class="form-control" placeholder="@lang('Enter name')" name="name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label font-weight-bold"> @lang('Description')</label>
                            <input type="text" class="form-control" placeholder="@lang('Enter Description.')" name="description" required>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label font-weight-bold"> @lang('Booking Permissions')</label>
                            <div class="custom-control custom-checkbox">
              								<input type="checkbox" id="add_booking" data-checktype="booking-permission" class="permissionsCheckbox custom-control-input" name="booking_permission[]" value="add_booking">
              								<label class="custom-control-label activityPermissionLabel" for="add_booking">Add Booking</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="payment_booking" data-checktype="booking-permission" class="permissionsCheckbox custom-control-input" name="booking_permission[]" value="payment_booking">
              								<label class="custom-control-label activityPermissionLabel" for="payment_booking">Make Payment</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="invoice_booking" data-checktype="booking-permission" class="permissionsCheckbox custom-control-input" name="booking_permission[]" value="invoice_booking">
              								<label class="custom-control-label activityPermissionLabel" for="invoice_booking">Invoice</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="refund_booking" data-checktype="booking-permission" class="permissionsCheckbox custom-control-input" name="booking_permission[]" value="refund_booking">
              								<label class="custom-control-label activityPermissionLabel" for="refund_booking">Refund</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="payment_details_booking" data-checktype="booking-permission" class="permissionsCheckbox custom-control-input" name="booking_permission[]" value="payment_details_booking">
              								<label class="custom-control-label activityPermissionLabel" for="payment_details_booking">Payment Details</label>
              							</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Edit METHOD MODAL --}}
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Update Role')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-control-label font-weight-bold"> @lang('Name')</label>
                            <input type="text" class="form-control" placeholder="@lang('Enter name')" name="name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label font-weight-bold"> @lang('Description')</label>
                            <input type="text" class="form-control" placeholder="@lang('Enter Description')" name="description" required>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label font-weight-bold"> @lang('Booking Permissions')</label>
                            <div class="custom-control custom-checkbox">
              								<input type="checkbox" id="eadd_booking" data-checktype="booking-permission" class="book_permissionsCheckbox custom-control-input" name="booking_permission_edit[]" value="add_booking">
              								<label class="custom-control-label" for="eadd_booking">Add Booking</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="epayment_booking" data-checktype="booking-permission" class="book_permissionsCheckbox custom-control-input" name="booking_permission_edit[]" value="payment_booking">
              								<label class="custom-control-label" for="epayment_booking">Make Payment</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="einvoice_booking" data-checktype="booking-permission" class="book_permissionsCheckbox custom-control-input" name="booking_permission_edit[]" value="invoice_booking">
              								<label class="custom-control-label" for="einvoice_booking">Invoice</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="erefund_booking" data-checktype="booking-permission" class="book_permissionsCheckbox custom-control-input" name="booking_permission_edit[]" value="refund_booking">
              								<label class="custom-control-label" for="erefund_booking">Refund</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                              <input type="checkbox" id="epayment_details_booking" data-checktype="booking-permission" class="book_permissionsCheckbox custom-control-input" name="booking_permission_edit[]" value="payment_details_booking">
              								<label class="custom-control-label" for="epayment_details_booking">Payment Details</label>
              							</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- active METHOD MODAL --}}
    <div id="activeModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Active Roles')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.roles.active.disable')}}" method="POST">
                    @csrf
                    <input type="text" name="id" hidden="true">
                    <div class="modal-body">
                        <p>@lang('Are you sure to active') <span class="font-weight-bold name"></span> @lang('Roles')?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Active')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- disable METHOD MODAL --}}
    <div id="disableModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Disable Roles')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.roles.active.disable')}}" method="POST">
                    @csrf
                    <input type="text" name="id" hidden="true">
                    <div class="modal-body">
                        <p>@lang('Are you sure to disable') <span class="font-weight-bold name"></span> @lang('Roles')?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--danger">@lang('Disable')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="javascript:void(0)" class="btn btn--primary box--shadow1 addBtn"><i class="fa fa-fw fa-plus"></i>@lang('Add New')</a>
    <form action="{{route('admin.roles.search') }}" method="GET" class="form-inline float-sm-right bg--white mb-2 ml-0 ml-xl-2 ml-lg-0">
        <div class="input-group has_append  ">
            <input type="text" name="search" class="form-control" placeholder="@lang('Name')" value="{{ $search ?? '' }}">
            <div class="input-group-append">
                <button class="btn btn--primary" type="submit"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </form>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        $('.disableBtn').on('click', function () {
            var modal = $('#disableModal');
            modal.find('input[name=id]').val($(this).data('id'));
            modal.find('.name').text($(this).data('name'));
            modal.modal('show');
        });

        $('.activeBtn').on('click', function () {
            var modal = $('#activeModal');
            modal.find('input[name=id]').val($(this).data('id'));
            modal.find('.name').text($(this).data('name'));
            modal.modal('show');
        });
        $('.addBtn').on('click', function () {
            var modal = $('#addModal');
            modal.modal('show');
        });

        $('.editBtn').on('click', function () {
            var modal = $('#editModal');
            modal.find('form').attr('action' ,$(this).data('action'));
            var category = $(this).data('category');
            modal.find('input[name=name]').val(category.name);
            modal.find('input[name=description]').val(category.description);
            modal.find($('.book_permissionsCheckbox')).prop('checked', false);

            if(category.role_permission != null)
            {
              var strings = category.role_permission;
              var substr  = strings.split(',');
              substr.forEach(function(item) {
                var checkboxes = $('input[name="booking_permission[]"]');
                modal.find($('input:checkbox[name="booking_permission_edit[]"][value="' + item + '"]').prop('checked',true));
                modal.find($('input:checkbox[name="booking_permission_edit[]"][value="' + item + '"]').prop('checked',true));
              });
            }
            //modal.find('.book_permissionsCheckbox').attr('checked', 'checked');
            modal.modal('show');
        });

    })(jQuery);

</script>
@endpush
