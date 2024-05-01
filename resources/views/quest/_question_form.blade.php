<legend>Questions <small>(drag and drop to add/remove)</small></legend>
{!! Form::hidden('question_list',implode(',',$quest->questions->pluck('id')->all())) !!}
<div class="table">
    <div class="table-cell-col-2">
        <div class="row">
            <div class="col-12">
                <h4>Current Questions <small><span class="drop-count">{{ $quest->questions->count() }}</span> questions</small></h4>
                <div class="card card-body" id="questionList">
                    @include('question._list',array('models'=>$quest->questions))
                </div>
            </div>
        </div>
    </div>
    <div class="table-cell-col-2">
        <div class="row">
            <div class="col-12">
                <h4>Available Questions</h4>
                <div class="card card-body" id="availableQuestions">
                    @include('question._list',array('models'=>$questions))
                </div>
            </div>
        </div>
    </div>
</div>