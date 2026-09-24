<div class="profile-header" style="grid-template-columns: repeat(3,1fr)">
    <div class="profile-header-text-box">
        <p>نام : {{ $item->name }}
            ({{ $item->Age() }})
            ({{ $item->sex }})
        </p>
        <p>نام خانوادگی : {{ $item->lastName }} </p>
        <p>شماره پرونده : {{$item->caseNumber}}</p>
    </div>

    <div class="profile-header-text-box">
        <p> تاریخ تولد : {{$item->birthday}} </p>
        <p>نام پدر : {{$item->fatherName}}</p>
        <p> شماره همراه : {{$item->phone}}</p>
    </div>

    <div class="profile-header-text-box">
        <p>کدملی : {{$item->nationalCode}}</p>
        <p>نام مادر : {{$item->motherName}} {{$item->motherLastName}} </p>
    </div>

</div>
