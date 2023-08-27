<!DOCTYPE html>
<html>
<head>
    <title>دائرة صحة محافظة كركوك</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  
</head>
<body style="direction: rtl;">
<div class="container">       
<div class="row">
    <div class="col-4 text-center py-4">

        <div>
       {{$config['first_title_ar']}}
        </div>
        <div>
        {{$config['second_title_ar']}}
        </div>
        <div>
        {{$config['third_title_ar']}}
        </div>
    </div>
    <div class="col-4 text-center py-4">

    @include('logo')
    
       
    <p>بطاقة الهلال الأحمر</p>
    </div>
    <div class="col-4 text-center py-4"> 
        <div>
       {{$config['first_title_kr']}}
        </div>
        <div>
        {{$config['second_title_kr']}}
        </div>
        <div>
        {{$config['third_title_kr']}}
        </div>
    </div>
    </div>
    <div class="row pb-3 text-center" >
    <div class="col-4"> 
    التسلسل:
    <span>{{$profile->no}}</span>
    </div>
    <div class="col-4">
    التاريخ:
    <span style="direction: ltr;">{{$profile->created_at}}</span>
    </div>
    <div class="col-4">
    رقم الوصل:
        <span>{{$profile->invoice_number}}</span>
    </div>
  </div>
  <div class="row text-center">
    <div>
        <h4 class="text-primary">   معلومات البطاقة</h4>
    </div>
  </div>
  <div class="row text-center py-2">
    <div class="col-6">
            <div>
            الاسم:   {{ $profile->husband_name }}
            </div>
            <div>
            بطاقة رقم:   {{ $profile->card_number }}
            </div>
            <div>
            أفراد العائلة:   {{ $profile->family_name }}
            </div>
            <div>
            الهاتف:   {{ $profile->husband_job }}
            </div>
            <div>
            العنوان:   {{ $profile->phone_number }}    
            </div>
    </div>   
  </div>
</div>
</body>
</html>
