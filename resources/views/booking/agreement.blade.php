<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>اقرار نامہ حج 2026</title>

<style>
body{
    direction: rtl;
    font-family: serif;
    background:#f5f5f5;
    margin:20px;
    line-height:2;
}

.page{
    background:white;
    border:2px solid #000;
    padding:25px;
    margin-bottom:40px;
}

h1,h2,h3{
    text-align:center;
    margin:5px;
}

p{
    text-align:justify;
}

.handwrite{
    border-bottom:1px solid #000;
    display:inline-block;
    min-width:180px;
    height:25px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    font-size:14px;
}

table, th, td{
    border:1px solid #000;
}

th, td{
    padding:8px;
    text-align:center;
}

.blank{
    height:40px;
}

.signature{
    margin-top:50px;
}
</style>
</head>

<body>
<input type="hidden"id="booking_id" value="{{ $booking->id }}">

<div class="page">

<h3>صفحہ نمبر : 8</h3>
<h1>اقرار نامہ</h1>
<h3>برائے حج 2026 ء (1447 ہجری)</h3>

میں
<span style="border-bottom:1px solid #000; display:inline-block; padding-bottom:2px;">
    {{$booking->client->name}}
</span>
ولد / زوجیت
<span class="handwrite"></span>
اور شناختی کارڈ نمبر
<span style="border-bottom:1px solid #000; display:inline-block; padding-bottom:2px;">
    {{$booking->client->cnic}}
</span>

<p>
میں یہ اقرار کرتا / کرتی ہوں کہ میں نے نجی کمپنی کا خود انتخاب کیا ہے اور
میں نے واجبات کمپنی آفس جا کر ادا کیے ہیں۔ کمپنی کے نمائندے نے حج سے متعلق
تمام امور و معلومات سے آگاہ کیا ہے۔
</p>

<p>
میں نے حج ٹریننگ پروگرام میں بھی شرکت کی ہے اور اپنی تمام معلومات،
وارث، CNIC اور فون نمبر خود فراہم کیے ہیں۔ میں اس کی مکمل ذمہ دار ہوں۔
</p>

<p>
میں نے کسی بھی ایجنٹ سے رابطہ نہیں کیا اور براہ راست کمپنی آفس جا کر
بکنگ کروائی ہے۔
</p>

<p>
میں نے حکومت پاکستان اور سعودی حکومت کی ہدایات کے مطابق تمام ویکسین
لگوا کر سرٹیفکیٹ کمپنی میں جمع کروائے ہیں۔
</p>

<p>
میں اپنے منتخب کردہ HGO کے ساتھ فریضہ حج ادا کر کے مقررہ تاریخ پر واپس
آؤں گا / آؤں گی اور کسی صورت مقررہ مدت سے زیادہ سعودی عرب میں قیام نہیں کروں گا / گی۔
</p>

<p>
میں نے کمپنی سے اپنے طے شدہ پیکج کے مطابق کمپیوٹرائزڈ معاہدہ اور رسید حاصل
کر لی ہے۔
</p>

<p>
میں نے وزارت کی ویب سائٹ www.mora.gov.pk پر کمپنی کی معلومات دیکھ کر
اس کمپنی کا انتخاب کیا ہے۔
</p>

{{-- <div class="signature">
<p>حاجی / حاجیہ دستخط : ________________________</p>
<p>چیف ایگزیکٹو دستخط : ________________________</p>
</div> --}}

<div class="signature">
    <div style="display:flex; align-items:flex-end; gap:10px;">

        <span>حاجی / حاجیہ دستخط :</span>

        <div style="display:inline-block; text-align:center;">
            <img id="signature-preview-1"
                 @if($booking->agreement_signature)
                    src="{{ asset('storage/'.$booking->agreement_signature) }}"
                    style="height:70px;"
                 @else
                    style="height:70px;display:none;"
                 @endif
            >

            <div style="border-top:1px solid #000; width:150px; margin-top:5px;"></div>
        </div>
    </div>
     {{-- <p style="margin-top:20px;">
        چیف ایگزیکٹو دستخط : ________________________
    </p> --}}
    <div style="display:flex; align-items:flex-end; gap:10px; margin-top:20px;">
    <span>چیف ایگزیکٹو دستخط :</span>

    <div style="text-align:center;">
        <img src="{{ asset('images/fake_signature.png') }}"
             alt="CEO Signature"
             style="height:70px;">

        <div style="border-top:1px solid #000; width:150px; margin-top:5px;"></div>
    </div>
</div>
</div>

{{-- <div class="signature">
    <p>
        حاجی / حاجیہ دستخط :
        <br>

        <img id="signature-preview-1"
             @if($booking->agreement_signature)
                src="{{ asset('storage/'.$booking->agreement_signature) }}"
                style="height:80px;"
             @else
                style="height:80px;display:none;"
             @endif
        >
    </p>

    <p>چیف ایگزیکٹو دستخط : ________________________</p>
</div> --}}

</div>


<div class="page">

<h3>صفحہ نمبر : 9</h3>

<table>
<tr>
<th>نمبر شمار</th>
<th>تفصیل</th>
<th>کیٹیگری</th>
</tr>

<tr>
<td>1</td>
<td>رہائش (Sharing Rooms)</td>
<td>D</td>
</tr>

<tr>
<td>2</td>
<td>مکہ مکرمہ میں رہائش تقریباً 200 میٹر فاصلے پر ہوگی۔</td>
<td></td>
</tr>

<tr>
<td>3</td>
<td>مدینہ منورہ میں رہائش تقریباً 300 میٹر فاصلے پر ہوگی۔</td>
<td></td>
</tr>

<tr>
<td>4</td>
<td>تمام حاجیوں کو سعودی قوانین کے مطابق سہولیات فراہم کی جائیں گی۔</td>
<td></td>
</tr>

<tr>
<td>5</td>
<td>ٹرانسپورٹ کی سہولت دستیاب ہوگی۔</td>
<td></td>
</tr>

<tr>
<td>6</td>
<td>حجاج کرام کیلئے کھانے کا انتظام ہوگا۔</td>
<td></td>
</tr>

<tr>
<td>7</td>
<td>اضافی سہولیات پر اضافی چارجز وصول کیے جائیں گے۔</td>
<td></td>
</tr>

<tr>
<td>8</td>
<td>مرد و خواتین کیلئے علیحدہ رہائش ہوگی۔</td>
<td></td>
</tr>

<tr>
<td>9</td>
<td>فلائٹ شیڈول سعودی حکومت کی منظوری کے مطابق ہوگا۔</td>
<td></td>
</tr>

<tr>
<td>10</td>
<td>قربانی سعودی قوانین کے مطابق ہوگی۔</td>
<td></td>
</tr>

<tr>
<td>11</td>
<td>حج موبائل ایپ کے استعمال کی مکمل رہنمائی دی جائے گی۔</td>
<td></td>
</tr>

<tr>
<td>12</td>
<td>کمپیوٹرائزڈ رسید فراہم کی جائے گی۔</td>
<td></td>
</tr>

<tr>
<td>13</td>
<td>دیگر شرائط و ضوابط لاگو ہوں گے۔</td>
<td></td>
</tr>

</table>

<br><br>

<table>
<tr>
<th>نام</th>
<th>والد / شوہر کا نام</th>
<th>شناختی کارڈ نمبر</th>
<th>موبائل نمبر</th>
</tr>

<tr class="blank">
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
</table>
<br>

     @if(!$booking->agreement_signature)

        <div class="signature-box">
            <label><strong>حاجی / حاجیہ دستخط</strong></label>

            <canvas id="signature-pad"
                width="500"
                height="200"
                style="border:1px solid #000; background:#fff;">
            </canvas>

            <br><br>

            <button type="button" onclick="clearSignature()">
                Clear Signature
            </button>

            <button type="button" onclick="saveSignature()">
                Save Signature
            </button>

            <input type="hidden" name="signature" id="signature">
        </div>

    @endif
{{-- <p>دستخط حاجی / حاجیہ : ________________________</p> --}}
<div style="display:flex; align-items:flex-end; gap:10px;">
    <span>دستخط حاجی / حاجیہ :</span>
    <div style="text-align:center;">
        <img id="signature-preview-2"
             @if($booking->agreement_signature)
                src="{{ asset('storage/'.$booking->agreement_signature) }}"
                style="height:70px;"
             @else
                style="height:80px;display:none;"
             @endif
        >
        <div style="border-top:1px solid #000; width:150px; margin-top:5px;"></div>
    </div>
</div>
{{-- <p>
    دستخط حاجی / حاجیہ :

    <img id="signature-preview-2"
         @if($booking->agreement_signature)
            src="{{ asset('storage/'.$booking->agreement_signature) }}"
            style="height:80px;"
         @else
            style="height:80px;display:none;"
         @endif
    >
</p> --}}
{{-- </p> --}}
{{-- <p>پاسپورٹ نمبر : ________________________ {{$booking->passport_number}}</p>
<p>موبائل نمبر : ________________________{{$booking->phone}}</p> --}}

<p>
    پاسپورٹ نمبر :
    <span style="display:inline-block; min-width:200px; border-bottom:1px solid #000;">
        {{ $booking->client->passport_number ?? '' }}
    </span>
</p>

<p>
    موبائل نمبر :
    <span style="display:inline-block; min-width:200px; border-bottom:1px solid #000;">
        {{ $booking->client->phone ?? '' }}
    </span>
</p>
<div style="display:flex; align-items:flex-end; gap:10px;">
    <span>چیف ایگزیکٹو دستخط :</span>

    <div style="text-align:center;">
        <img src="{{ asset('images/fake_signature.png') }}"
             alt="CEO Signature"
             style="height:70px;">

        <div style="border-top:1px solid #000; width:150px; margin-top:5px;"></div>
    </div>
</div>
<p>
قربانی سعودی حکومت کی مقرر کردہ پالیسی کے مطابق ہوگی اور اس کی رقم متعلقہ کمپنی کو ادا کی جائے گی۔
اگر کوئی حاجی قوانین کی خلاف ورزی کرتا پایا گیا تو اس کے خلاف قانونی کارروائی ہو سکتی ہے۔
</p>
</body>
<script>
const canvas = document.getElementById('signature-pad');
const ctx = canvas.getContext('2d');

let drawing = false;

function getPosition(e) {
    const rect = canvas.getBoundingClientRect();

    if (e.touches) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    }

    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

function startDrawing(e) {
    drawing = true;

    const pos = getPosition(e);

    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
}

function draw(e) {
    if (!drawing) return;

    e.preventDefault();

    const pos = getPosition(e);

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
}

function stopDrawing() {
    drawing = false;
}

canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseleave', stopDrawing);

canvas.addEventListener('touchstart', startDrawing);
canvas.addEventListener('touchmove', draw);
canvas.addEventListener('touchend', stopDrawing);

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveSignature() {

    const imageData = canvas.toDataURL('image/png');

    // Dono jagah same signature show karo
    document.getElementById('signature-preview-1').src = imageData;
    document.getElementById('signature-preview-2').src = imageData;

    document.getElementById('signature-preview-1').style.display = 'block';
    document.getElementById('signature-preview-2').style.display = 'block';

    const bookingId =
        document.getElementById('booking_id').value;

    fetch('/save-agreement-signature', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector(
                'meta[name="csrf-token"]'
            ).content
        },
        body: JSON.stringify({
            booking_id: bookingId,
            signature: imageData
        })
    })
    .then(response => response.json())
    .then(data => {

        if(data.success){
            alert('Signature Saved Successfully');

            // canvas hide kar do
            document.querySelector('.signature-box').style.display = 'none';
        }

    })
    .catch(error => {
        console.error(error);
        alert('Error saving signature');
    });

}
</script>
</html>
