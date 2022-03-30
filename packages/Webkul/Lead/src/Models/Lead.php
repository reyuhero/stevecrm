<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Webkul\Activity\Models\ActivityProxy;
use Webkul\Contact\Models\PersonProxy;
use Webkul\User\Models\UserProxy;
use Webkul\Email\Models\EmailProxy;
use Webkul\Quote\Models\QuoteProxy;
use Webkul\Tag\Models\TagProxy;
use Webkul\Attribute\Traits\CustomAttribute;
use Webkul\Lead\Contracts\Lead as LeadContract;

class Lead extends Model implements LeadContract
{
    use CustomAttribute;

    protected $casts = [
        'closed_at'           => 'datetime',
        'expected_close_date' => 'date',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'lead_value',
        'status',
        'lost_reason',
        'location',
        'expected_close_date',
        'closed_at',
        'person_id',
        'user_id',
        'csr',
        'lead_source_id',
        'lead_type_id',
        'lead_pipeline_id',
        'lead_pipeline_stage_id',
    ];


    /**
     * Get the user that owns the lead.
     */
    public function user()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'user_id');
    }
    /**
     * Get the user that owns the lead.
     */
    public function person()
    {
        return $this->belongsTo(PersonProxy::modelClass(), 'person_id');
    }

    /**
     * Get the csr that owns the lead.
     */
    public function csr()
    {
        return $this->belongsTo(UserProxy::modelClass(), 'csr');
    }

    /**
     * Get the type that owns the lead.
     */
    public function type()
    {
        return $this->belongsTo(TypeProxy::modelClass());
    }

    /**
     * Get the source that owns the lead.
     */
    public function source()
    {
        return $this->belongsTo(SourceProxy::modelClass());
    }

    /**
     * Get the pipeline that owns the lead.
     */
    public function pipeline()
    {
        return $this->belongsTo(PipelineProxy::modelClass(), 'lead_pipeline_id');
    }

    /**
     * Get the pipeline stage that owns the lead.
     */
    public function stage()
    {
        return $this->belongsTo(StageProxy::modelClass(), 'lead_pipeline_stage_id');
    }

    /**
     * Get the activities.
     */
    public function activities()
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'lead_activities');
    }


    /**
     * Get the emails.
     */
    public function emails()
    {
        return $this->hasMany(EmailProxy::modelClass());
    }

    /**
     * The quotes that belong to the lead.
     */
    public function quotes()
    {
        return $this->belongsToMany(QuoteProxy::modelClass(), 'lead_quotes');
    }

    /**
     * The tags that belong to the lead.
     */
    public function tags()
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'lead_tags');
    }

    /**
     * Returns the rotten days
     */
    public function getRottenDaysAttribute()
    {
        if (in_array($this->stage->code, ['won', 'lost'])) {
            return 0;
        }

        $rottenDate = $this->created_at->addDays($this->pipeline->rotten_days);

        return $rottenDate->diffInDays(Carbon::now(), false);
    }
}
