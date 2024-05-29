<div class="row">
    <!-- First Name -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('First name', 'first_name') }}
        {{ html()->text('first_name')->class('form-control')->required() }}
    </div>

    <!-- Last Name -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Last name', 'last_name') }}
        {{ html()->text('last_name')->class('form-control')->required() }}
    </div>

    <!-- Played -->
    <div class="form-group col-xs-12 col-sm-4">
        <div class="checkbox">
            <label>
                {{ html()->checkbox('checked_in', false) }} Played?
            </label>
        </div>
    </div>
</div>

<div class="row">
    <!-- Email -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Email', 'email') }}
        {{ html()->text('email')->class('form-control')->required() }}
    </div>

    @if($player->manual)

        <!-- Password -->
        <div class="form-group col-xs-12 col-sm-8">
            {{ html()->label('Change Password', 'password') }}
            {{ html()->text('password', '')->class('form-control') }}
        </div>

    @else

        <!-- Onyen -->
        <div class="form-group col-xs-12 col-sm-4">
            {{ html()->label('Onyen', 'onyen') }}
            {{ html()->text('onyen')->class('form-control')->required() }}
        </div>


        <!-- PID -->
        <div class="form-group col-xs-12 col-sm-4">
            {{ html()->label('PID', 'pid') }}
            {{ html()->text('pid')->class('form-control')->required() }}
        </div>

    @endif
</div>

<div class="row">
    <!-- Class -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Class', 'class_code') }}
        {{ html()->select('class_code', $player::CLASS_OPTIONS, $player->class_code)->class('form-control')->required() }}
    </div>

    <!-- Group -->
    <div class="form-group col-xs-12 col-sm-4">
        {{ html()->label('Academic Group', 'academic_group_code') }}
        {{ html()->select('academic_group_code', $player::ACADEMIC_GROUP_OPTIONS, $player->academic_group_code)->class('form-control')->required() }}
    </div>

</div>

<div class="row">
    <div class="col-xs-12 form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>