<div class="row align-items-center">
    <!-- First Name -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('first_name', 'First name', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('first_name', null, array('class'=>'form-control', 'required')) !!}
    </div>

    <!-- Last Name -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('last_name', 'Last name', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('last_name', null, array('class'=>'form-control', 'required')) !!}
    </div>

    <!-- Played -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {!! Form::checkbox('checked_in') !!} Played?
            </label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Email -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('email', 'Email', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::text('email', null, array('class'=>'form-control', 'required')) !!}
    </div>

    @if($player->manual)

        <!-- Password -->
        <div class="form-group col-12 col-sm-8">
            {!! Form::label('password', 'Change Password', ['class' => 'fw-bold mb-1']) !!}
            {!! Form::text('password', '', array('class'=>'form-control')) !!}
        </div>

    @else

        <!-- Onyen -->
        <div class="form-group col-12 col-sm-4">
            {!! Form::label('onyen', 'Onyen', ['class' => 'fw-bold mb-1']) !!}
            {!! Form::text('onyen', null, array('class'=>'form-control', 'required')) !!}
        </div>


        <!-- PID -->
        <div class="form-group col-12 col-sm-4">
            {!! Form::label('pid', 'PID', ['class' => 'fw-bold mb-1']) !!}
            {!! Form::text('pid', null, array('class'=>'form-control', 'required')) !!}
        </div>

    @endif
</div>

<div class="row mt-3">
    <!-- Class -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('class_code', 'Class', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::select('class_code', $player::CLASS_OPTIONS, $player->class_code, array('class'=>'form-control', 'required')) !!}
    </div>

    <!-- Group -->
    <div class="form-group col-12 col-sm-4">
        {!! Form::label('academic_group_code', 'Academic Group', ['class' => 'fw-bold mb-1']) !!}
        {!! Form::select('academic_group_code', $player::ACADEMIC_GROUP_OPTIONS, $player->academic_group_code, array('class'=>'form-control', 'required')) !!}
    </div>

</div>

<div class="row mt-3">
    <div class="col-12 form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>