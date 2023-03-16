$(document).ready(function() {

    // $("#detailpay" ).hide();
    // $("#payment_method").hide();

    var baseurl = $('#baseurl').val();


    $('Button').click(function() {
        var id = $(this).attr('id');
        var baseurl = $('#baseurl').val();
        var journeydate = $('#journeydate').val();
        var url = baseurl + '/modules/backend/tickets/singletrip/'+ id+'/'+journeydate ;
        var seat = null;
        var subtrip = null;
        var picdrop = null;
        var taxval = null;
        var childprice = null;
        var adultprice = null;
        var spacialprice = null;
        var taxarray = [];



        $.ajax({
            method: "GET",
            url: url,
            dataType: "JSON",

            success: function(result) {

                $("#form_" + id).empty();
                $("#seat_" + id).empty();


                var html = dynamichtml(result, id);
                var seathtml = dynamicseat(result, id);


                $("#form_" + id).append(html);
                $("#seat_" + id).append(seathtml);


            }

        });



    });



    $("#partial").hide();

    $("#payment_status").change(function() {

        var paystats = $("#payment_status").val();

        if ((paystats == "paid")) {
            $("#detailpay").show();
            $("#payment_method").show();
            $("#partial").hide();
            $("#couponcode").show();
            $("#less").show();
        }

        if ((paystats == "partial")) {

            var payamount = $("#grandtotal").val();

            $("#detailpay").show();
            $("#payment_method").show();
            $("#partial").show();
            $("#partialpay").val(payamount);
            $("#couponcode").show();
            $("#discount").show();
        }

        if (paystats == "unpaid") {
            var oldGrandtotal = $("#oldgrandtotal").val();
            $("#discount").attr('readonly', false);
            $("#coupon").attr('readonly', false);
            $("#detailpay").hide();
            $("#payment_method").hide();
            $("#couponcode").hide();
            $("#less").hide();
            $("#grandtotal").val(oldGrandtotal);
            $("#discount").val(0);
            $("#partialpay").val(oldGrandtotal);
            $("#coupon").val("");
            $("#couponmessage").html("");
            $("#partial").val("");
            $("#partial").hide();
        }
    });


    $("#discount").focusout(function() {

        var discount = $("#discount").val();
        var oldtotal = $("#oldgrandtotal").val();
        discount = parseFloat(discount)
        if (discount == 0) {
            $("#grandtotal").val(oldtotal);
            $("#partialpay").val(oldtotal);
        } else {
            var newgrandtotal = parseFloat(oldtotal) - discount;

            $("#grandtotal").val(newgrandtotal);
            $("#partialpay").val(newgrandtotal);

        }







    });




});

function dynamicseat(result, id) {
    var seatlist = [];
    var bookseat = [];
    var seatnumber = null;
    subtrip = JSON.parse(result.subtrips);
    seatbook = JSON.parse(result.bookseat);
    

    $.each(subtrip, function(index, value) {
        seatnumber = value.seat_number

    });

    var seatarray = seatnumber.split(',');
    var bookseatarray = seatbook.split(',');

    seatarray.forEach((value) => {

        seatlist.push(value);

    });

    bookseatarray.forEach((svalue) => {

        bookseat.push(svalue);

    });

    console.log(typeof bookseat);



    var html = '<div class="row">';

    // seatlist.forEach((value) => {

    //     html += '<div class="col-2 mt-3">';
    //     html += '<h6 id="seat_' + id + '_' + value + '" name="seat_' + id + '_' + value + '" class="btn btn-light" onclick="seacClick(this,' + id + ')">' + value + '</h6>';
    //     html += '</div>';

    // });

    seatlist.forEach((value) => {

        if (bookseatarray.includes(value)) {

            html += '<div class="col-2 mt-3">';
            html += '<h6 id="seat_' + id + '_' + value + '" name="seat_' + id + '_' + value + '" class="pe-none btn btn-danger">' + value + '</h6>';
            html += '</div>';
            
        } 
        else {

            html += '<div class="col-2 mt-3">';
            html += '<h6 id="seat_' + id + '_' + value + '" name="seat_' + id + '_' + value + '" class="btn btn-light" onclick="seacClick(this,' + id + ')">' + value + '</h6>';
            html += '</div>';
            
        }

       

    });

    html += '<input type="hidden" id="seatnumber' + id + '" name="seatnumber' + id + '[]" value="" >';

    html += '</div>';


    return html;
}




function seacClick(seatindex, subtripid) {

    var seatArray = [];
    var seatvalue = $("#seatnumber" + subtripid).val();

    var id = seatindex.getAttribute('id');
    var numberseat = $("#" + id).text();

    if (sessionStorage.getItem("tripid")) {

        if (sessionStorage.getItem("tripid") != subtripid) {

            var removeid = sessionStorage.getItem("tripid");
            sessionStorage.removeItem('tripid')
            var removeclass = [];
            var removeseat = $("#seatnumber" + removeid).val();
            if (removeseat != "") {

                removeclass = removeseat.split(',');
            }
            console.log(typeof removeclass);

            removeclass = jQuery.grep(removeclass, function(value) {

                $("#seat_" + removeid + '_' + value).removeClass("btn btn-success").addClass("btn btn-light");
            });


            var newseatValue = "";
            $("#seatnumber" + removeid).val(newseatValue);

        }

    }

    if (seatvalue == "") {


        $("#seatnumber" + subtripid).val(numberseat);

        sessionStorage.removeItem('tripid')
        sessionStorage.setItem("tripid", subtripid);
        $("#" + id).removeClass("btn btn-light").addClass("btn btn-success");



    } else {


        if ((sessionStorage.getItem("tripid")) && (sessionStorage.getItem("tripid") == subtripid)) {


            var totalseat = [];
            totalseat = seatvalue.split(',');
            console.log(typeof totalseat);

            var hasVal = Object.values(totalseat).includes(numberseat);

            if (hasVal) {
                totalseat = jQuery.grep(totalseat, function(value) {

                    return value != numberseat;
                });

                $("#" + id).removeClass(" btn btn-success").addClass("btn btn-light");
                let newSeats = totalseat.toString();
                $("#seatnumber" + subtripid).val(newSeats);



            } else {

                totalseat.push(numberseat);
                let newSeats = totalseat.toString();
                $("#seatnumber" + subtripid).val(newSeats);
                $("#" + id).removeClass("btn btn-light").addClass("btn btn-success");
                console.log(newSeats)
            }

        } else {

            sessionStorage.removeItem('tripid')
            sessionStorage.setItem("tripid", subtripid);
            let newSeats = "";
            $("#seatnumber" + subtripid).val(newSeats);
            $("#seatnumber" + subtripid).val(numberseat);
            $("#" + id).removeClass("btn btn-light").addClass("btn btn-success");
            console.log(numberseat);

        }




    }


}


function dynamichtml(result, id) {
    var seat = null;
    var subtrip = null;
    var picdrop = null;
    var taxval = null;
    var childprice = null;
    var adultprice = null;
    var spacialprice = null;
    var taxarray = [];



    // $( "#" + picid ).remove();
    subtrip = JSON.parse(result.subtrips);
    picdrop = JSON.parse(result.pickdrop);
    taxval = JSON.parse(result.tax);



    $.each(subtrip, function(index, value) {
        spacialprice = value.special_fair
        adultprice = value.adult_fair
        childprice = value.child_fair

    });


    $.each(picdrop, function(index, pvalue) {

        console.log(pvalue);
    });

    $.each(taxval, function(index, taxvalue) {

        taxarray.push(taxvalue.value);
    });



    

    var html = '<div class="row">';


    html += '<div class="col-4">';
    html += '<label for="child_seat" class="form-label">Children Seat</label>';
    html += ' <input type="number" id="child_seat' + id + '" name="child_seat" value="0" class="form-control"  min="1" onkeyup="childseat(this,' + id + ')">';
    html += '</div>';

    html += '<div class="col-4">';
    html += '<label for="special_seat" class="form-label">Special Seat</label>';
    html += ' <input type="number" id="special_seat' + id + '" name="special_seat" value="0" class="form-control"  min="1" onkeyup="specialseat(this,' + id + ')">';
    html += '</div>';


    html += '<div class="col-4">';
    html += '<label for="adult_seat" class="form-label">Adult Seat</label>';
    html += ' <input type="number" id="adult_seat' + id + '" name="adult_seat" value="0" class="form-control"   min="1" onkeyup="adultseat(this,' + id + ')">';
    html += '</div>';



    html += '<div class="col-6 mt-2">';
    html += '<label for="pickupstand" class="form-label">Pickup Bus Stand</label>';
    html += '<select  name="pickupstand' + id + '" id = "pickupstand' + id + '" class="form-select testselect1" required>';
    html += '<option value="" >None</option>';
    $.each(picdrop, function(index, pvalue) {
        if (pvalue.type == 1) {
            html += '<option value="' + pvalue.pickdropid + '" >' + pvalue.name + '-' + pvalue.time + '</option>';
        }
    });
    html += '</select>';
    html += '</div>';



    html += '<div class="col-6 mt-2">';
    html += '<label for="dropstand" class="form-label">Drop Bus Stand</label>';
    html += '<select  name="dropstand' + id + '" id = "dropstand' + id + '"  class="form-select testselect1" required>';
    html += '<option value="" >None</option>';
    $.each(picdrop, function(index, pvalue) {
        if (pvalue.type == 0) {
            html += '<option value="' + pvalue.pickdropid + '" >' + pvalue.name + '-' + pvalue.time + '</option>';
        }
    });
    html += '</select>';
    html += '</div>';


    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label">Type</h6>';
    html += '</div>';

    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label">Price</h6>';
    html += '</div>';

    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label">Total</h6>';
    html += '</div>';


    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label">Adult</h6>';
    html += '</div>';

    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label" id="adult_price' + id + '">' + adultprice + '</h6>';
    html += '</div>';

    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label" id="adult_price_show' + id + '">0</h6>';
    html += '</div>';



    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label">Child</h6>';
    html += '</div>';

    if (childprice == "") {

        html += '<div class="col-4 mt-2">';
        html += '<h6  class="form-label" id="child_price' + id + '">' + adultprice + '</h6>';
        html += '</div>';
    } else {
        html += '<div class="col-4 mt-2">';
        html += '<h6  class="form-label" id="child_price' + id + '">' + childprice + '</h6>';
        html += '</div>';

    }

    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label" id="child_price_show' + id + '">0</h6>';
    html += '</div>';



    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label">Special</h6>';
    html += '</div>';

    if (spacialprice == "") {

        html += '<div class="col-4 mt-2">';
        html += '<h6  class="form-label" id="special_price' + id + '">' + adultprice + '</h6>';
        adultprice
        html += '</div>';
    } else {
        html += '<div class="col-4 mt-2">';
        html += '<h6  class="form-label" id="special_price' + id + '">' + spacialprice + '</h6>';
        html += '</div>';

    }

    html += '<div class="col-4 mt-2">';
    html += '<h6  class="form-label" id="special_price_show' + id + '">0</h6>';
    html += '</div>';

    html += '<hr>';


    html += '<div class="col-md-4">';
    html += '<h6  class="form-label" >Ticket Price</h6>';
    html += '</div>';

    html += '<div class="col-md-4">';
    html += '<h6  class="form-label" ></h6>';
    html += '</div>';

    html += '<div class="col-md-4 ">';
    html += '<h6  class="form-label" id="totalprice' + id + '">0</h6>';
    html += '</div>';



    html += '<div class="col-md-4">';
    html += '<h6  class="form-label" >Tax</h6>';
    html += '</div>';

    html += '<div class="col-md-4">';
    html += '<h6  class="form-label" ></h6>';
    html += '</div>';

    html += '<div class="col-md-4 ">';
    html += '<h6  class="form-label" id="totaltax' + id + '">0</h6>';
    html += '</div>';



    html += '<div class="col-md-4">';
    html += '<h6  class="form-label" >Grand Total</h6>';
    html += '</div>';

    html += '<div class="col-md-4">';
    html += '<h6  class="form-label" ></h6>';
    html += '</div>';

    html += '<div class="col-md-4 ">';
    html += '<h6  class="form-label" id="grandtotal' + id + '">0</h6>';
    html += '</div>';




    html += '<input type="hidden" id="tax' + id + '" name="tax' + id + '[]" value ="' + taxarray + '">';


    html += '</div>';



    return html;

}







function childseat(cseat, subtripid) {
    
    $("#child_seat" + subtripid).on("input", function() {
        var nonNumReg = /[^0-9]/g;
        $(this).val($(this).val().replace(nonNumReg, ''));
      });

    var id = cseat.getAttribute('id');
    var numberseat = $("#" + id).val();
    var price = $("#child_price" + subtripid).text();
    var totalprice = parseInt(numberseat) * parseInt(price);
    $("#child_price_show" + subtripid).text(totalprice);
    toprice(subtripid);

}

function adultseat(cseat, subtripid) {

    $("#adult_seat" + subtripid).on("input", function() {
        var nonNumReg = /[^0-9]/g;
        $(this).val($(this).val().replace(nonNumReg, ''));
      });

    var id = cseat.getAttribute('id');
    var numberseat = $("#" + id).val();
    var price = $("#adult_price" + subtripid).text();
    var totalprice = parseInt(numberseat) * parseInt(price);
    $("#adult_price_show" + subtripid).text(totalprice);
    toprice(subtripid);
}

function specialseat(cseat, subtripid) {

    $("#special_seat" + subtripid).on("input", function() {
        var nonNumReg = /[^0-9]/g;
        $(this).val($(this).val().replace(nonNumReg, ''));
      });

    var id = cseat.getAttribute('id');
    var numberseat = $("#" + id).val();
    var price = $("#special_price" + subtripid).text();
    var totalprice = parseInt(numberseat) * parseInt(price);
    $("#special_price_show" + subtripid).text(totalprice);
    toprice(subtripid);
}

function toprice(subtripid) {
    var cprice = $("#special_price_show" + subtripid).text();
    var sprice = $("#child_price_show" + subtripid).text();
    var aprice = $("#adult_price_show" + subtripid).text();
    var totalprice = parseInt(cprice) + parseInt(sprice) + parseInt(aprice);
    $("#totalprice" + subtripid).text(totalprice);

    taxcaltulation(subtripid, totalprice);
}

function taxcaltulation(subtripid, totalvalue) {

    var taxvalues = $("#tax" + subtripid).val();
    var totaltaxval = [];
    var newtaxarray = taxvalues.split(',');

    newtaxarray.forEach((value) => {
        var caltax = (value * totalvalue) / 100
        totaltaxval.push(caltax);

    });

    var total = 0;
    totaltaxval.forEach((value) => {
        console.log(value);
        total += parseFloat(value);

    });
    var taxtype =  $("#tax_type").val();
    var grandtotal

    if (taxtype == "inclusive") {
        grandtotal =  parseFloat(totalvalue) - parseFloat(total);
        grandtotal  = totalvalue ;
       
    }
     else {
        grandtotal = parseFloat(total) + parseFloat(totalvalue);
        grandtotal  = parseFloat(grandtotal).toFixed(2) ;
    }

   
    $("#totaltax" + subtripid).text(total);
    $("#grandtotal" + subtripid).text(grandtotal);
}


function formsubmit(index, subtripid) {


    var allseatnumber = $("#seatnumber" + subtripid).val();

    var childseat = $("#child_seat" + subtripid).val();
    var specialseat = $("#special_seat" + subtripid).val();
    var adultseat = $("#adult_seat" + subtripid).val();

    

    var totalprice = $("#totalprice" + subtripid).text();
    var tax = $("#totaltax" + subtripid).text();
    var grandtotal = $("#grandtotal" + subtripid).text();

    var pickstand = $("#pickupstand" + subtripid).val();
    var dropstand = $("#dropstand" + subtripid).val();


    totalprice = parseInt(totalprice);
    tax = parseInt(tax);
    grandtotal = parseInt(grandtotal);

    childseat = parseInt(childseat);
    specialseat = parseInt(specialseat);
    adultseat = parseInt(adultseat);
   
   var totalpassanger = parseInt(childseat + adultseat + specialseat);

    if (allseatnumber) {
        $("#seatnumbers").val(allseatnumber);
    } else {
        alert("no seat selected");
        return false
    }

    if (totalpassanger <= 0) {
        alert("please enter passagenr");
        return false
    } else {
        $("#aseat").val(adultseat);
        $("#spseat").val(specialseat);
        $("#cseat").val(childseat);
    }

    if (pickstand) {
        $("#pickstand").val(pickstand);
    } else {
        alert("Please select pick stand");
        return false
    }


    if (dropstand) {
        $("#dropstand").val(dropstand);
    } else {
        alert("Please select drop stand");
        return false
    }
   
    var totalseatarray = allseatnumber.split(',');;
    var seatlength = totalseatarray.length
   
    
    
    if (totalpassanger != seatlength) {
        alert("Selected seat and passenger number are not matching");
        return false
    }
   
    


    $("#subtripId").val(subtripid);

    $("#totalprice").val(totalprice);
    $("#tax").val(tax);
    $("#grandtotal").val(grandtotal);

    $("#booking").submit();


}