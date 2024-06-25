<div class="row align-items-center">
    <!-- First Name -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('First name', 'first_name')->class('fw-bold mb-1') }}
        {{ html()->text('first_name')->class('form-control')->required() }}
    </div>

    <!-- Last Name -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('Last name', 'last_name')->class('fw-bold mb-1') }}
        {{ html()->text('last_name')->class('form-control')->required() }}
    </div>

    <!-- Played -->
    <div class="form-group col-12 col-sm-4">
        <div class="form-check">
            <label>
                {{ html()->checkbox('checked_in', false) }} Played?
            </label>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Email -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('Email', 'email')->class('fw-bold mb-1') }}
        {{ html()->text('email')->class('form-control')->required() }}
    </div>

    @if($player->manual)

        <!-- Password -->
        <div class="form-group col-12 col-sm-8">
            {{ html()->label('Change Password', 'password')->class('fw-bold mb-1') }}
            {{ html()->text('password', '')->class('form-control') }}
        </div>

    @else

        <!-- Onyen -->
        <div class="form-group col-12 col-sm-4">
            {{ html()->label('Onyen', 'onyen')->class('fw-bold mb-1') }}
            {{ html()->text('onyen')->class('form-control')->required() }}
        </div>


        <!-- PID -->
        <div class="form-group col-12 col-sm-4">
            {{ html()->label('PID', 'pid')->class('fw-bold mb-1')  }}
            {{ html()->text('pid')->class('form-control')->required() }}
        </div>

    @endif
</div>

<div class="row mt-3">
    <!-- Class -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('Class', 'class_code')->class('fw-bold mb-1')  }}
        {{ html()->select('class_code', $player::CLASS_OPTIONS, $player->class_code)->class('form-control')->required() }}
    </div>

    <!-- Group -->
    <div class="form-group col-12 col-sm-4">
        {{ html()->label('Academic Group', 'academic_group_code')->class('fw-bold mb-1')  }}
        {{ html()->select('academic_group_code', $player::ACADEMIC_GROUP_OPTIONS, $player->academic_group_code)->class('form-control')->required() }}
    </div>

</div>

<div class="row mt-3">
    <div class="col-12 form-group">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>