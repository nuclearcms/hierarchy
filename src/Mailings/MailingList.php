<?php


namespace Nuclear\Hierarchy\Mailings;

use Illuminate\Database\Eloquent\Model;
use Kenarkose\Chronicle\RecordsActivity;
use Kenarkose\Sortable\Sortable;
use Nicolaslopezj\Searchable\SearchableTrait;
use Nuclear\Hierarchy\MailingNode;

class MailingList extends Model {

    use Sortable, SearchableTrait, RecordsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'type',
        'from_name', 'reply_to', 'options', 'external_id'];

    /**
     * Sortable columns
     *
     * @var array
     */
    protected $sortableColumns = ['name', 'created_at'];

    /**
     * Default sortable key
     *
     * @var string
     */
    protected $sortableKey = 'name';

    /**
     * Default sortable direction
     *
     * @var string
     */
    protected $sortableDirection = 'asc';

    /**
     * Searchable columns.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'name'  => 10
        ]
    ];

    /**
     * Lists relation
     *
     * @return Relation
     */
    public function mailings()
    {
        return $this->belongsToMany(MailingNode::class, 'mailing_list_node', 'mailing_list_id', 'node_id');
    }

    /**
     * Subscribers relation
     *
     * @return Relation
     */
    public function subscribers()
    {
        return $this->belongsToMany(Subscriber::class, 'mailing_list_subscriber');
    }

    /**
     * Returns all subscriber mails
     *
     * @return array
     */
    public function getSubscriberAddresses()
    {
        return $this->subscribers->pluck('email')->toArray();
    }

}