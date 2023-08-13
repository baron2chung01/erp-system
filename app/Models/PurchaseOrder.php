<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PurchaseOrder
 * @package App\Models
 * @version July 2, 2022, 7:19 am UTC
 *
 * @property integer $quotation_id
 * @property string $po_number
 * @property string $name
 * @property string $description
 * @property string $shipping_address
 * @property string $contact_name
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class PurchaseOrder extends BaseModel
{
    use SoftDeletes;

    use HasFactory;

    const COMPANY_A = 0;
    const COMPANY_B = 1;
    const COMPANY_C = 2;
    const LHEAD = [
        self::COMPANY_A => 'COMPANY_A',
        self::COMPANY_B => 'COMPANY_B',
        self::COMPANY_C => 'COMPANY_C',
    ];

    const QUOTATION = 0;
    const SUPPLIER_PO = 1;
    const INVOICE = 2;
    const RECEIPT = 3;
    const ASSET = [
        self::QUOTATION   => 'quotation',
        self::SUPPLIER_PO => 'supplier-po',
        self::INVOICE     => 'invoice',
        self::RECEIPT     => 'receipt',
    ];

    const DRAFT = 0;
    const FORMAL = 1;
    const QUOT_STATUS = [
        self::DRAFT  => 'Draft',
        self::FORMAL => 'Formal',
    ];

    const FIXED = 0;
    const PERC = 1;
    const DISCOUNT_TYPE = [
        self::FIXED => 'Fixed',
        self::PERC  => 'Percentage',
    ];

    const DELETED = -1;

    const CONTRACT_STATUS = [
        0  => '草稿',
        1  => '1.1: 已報價，待客確認',
        2  => '1.2: 已收PO，安排開工',
        3  => '1.3: 已完工，欠維修報告，欠有完工相',
        4  => '1.4: 已完工，已簽維修報告，欠有完工相',
        5  => '1.5: 已完工，欠簽維修報告，已有完工相',
        6  => '1.6: 已完工，已簽維修報告，已有完工相，待上單',
        7  => '1.7: 已上單',
        8  => '1.8: 已收款',
        9  => '1.9: 完成PO',
        -1 => '已取消',
    ];

    const NOT_CONTRACT_STATUS = [
        0  => '草稿',
        1  => '2.1: 未收PO，安排開工',
        2  => '2.2: 未收PO，已完工，欠簽維修報告，欠有完工相',
        3  => '2.3: 未收PO，已完工，已簽維修報告，欠有完工相',
        4  => '2.4: 未收PO，已完工，欠簽維修報告，已有完工相',
        5  => '2.5: 未收PO，已完工，已簽維修報告，已有完工相',
        -1 => '已刪除',
    ];

    public $table = 'purchase_orders';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'id',
        'outline_agreement_id',
        'location_id',
        'quotation_id',
        'po_number',
        'name',
        'description',
        'shipping_address',
        'contact_name',
        'is_contract',
        'status',
        'quotation_remark',
        'revise_count',
        'issued_at',
        'received_at',
        'date_to_AC_dept',
        'qty',
        'expect_delivered_at',
        'expect_completed_at',
        'actual_completed_at',
        'person_in_charge',
        'invoice_date',
        'invoice_no',
        'quot_ref',
        'quot_date',
        'quot_letterhead',
        'discount_type',
        'discount_value',
        'total_price',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'                   => 'integer',
        'outline_agreement_id' => 'integer',
        'quotation_id'         => 'integer',
        'po_number'            => 'string',
        'name'                 => 'string',
        'description'          => 'string',
        'shipping_address'     => 'string',
        'contact_name'         => 'string',
        'status'               => 'integer',
        'quotation_remark'     => 'string',
        'revise_count'         => 'integer',
        'created_by'           => 'integer',
        'updated_by'           => 'integer',
        'deleted_by'           => 'integer',
        'issued_at'            => 'datetime',
        'received_at'          => 'datetime',
        'date_to_AC_dept'      => 'date',
        'qty'                  => 'integer',
        'expect_delivered_at'  => 'datetime',
        'expect_completed_at'  => 'datetime',
        'actual_completed_at'  => 'datetime',
        'person_in_charge'     => 'string',
        'invoice_date'         => 'date',
        'invoice_no'           => 'string',
        'quot_ref'             => 'string',
        'quot_date'            => 'datetime',

    ];

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'purchase_orders_has_clients', 'purchase_order_id', 'client_id');
    }

    public function contactPeople()
    {
        return $this->belongsToMany(ContactPerson::class, 'purchase_orders_has_contact_people', 'purchase_order_id', 'contact_person_id');
    }

    public function quotation()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::QUOTATION)->latest();
    }

    public function quotations()
    {
        // TODO: orderBy to be changed to updated_at
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::QUOTATION)->orderBy('file_name', 'desc');
    }

    public function supplierPOs()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::SUPPLIER_PO);
    }

    public function invoices()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::INVOICE);
    }

    public function receipts()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::RECEIPT);
    }

    public function signedQuotations()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::QUOTATION)->where('status', ASSET::SIGNED);
    }

    public function signedSupplierPOs()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::SUPPLIER_PO)->where('status', ASSET::SIGNED);
    }

    public function signedInvoices()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::INVOICE)->where('status', ASSET::SIGNED);
    }

    public function signedReceipts()
    {
        return $this->hasMany(Asset::class, 'related_id', 'id')->where('related_type', get_class($this))->where('asset_type', self::RECEIPT)->where('status', ASSET::SIGNED);
    }

    public function outlineAgreement()
    {
        return $this->belongsTo(OutlineAgreement::class);
    }

    public function poHasGroupTasks()
    {
        return $this->belongsToMany(GroupTask::class, 'purchase_orders_has_group_tasks', 'purchase_order_id', 'group_task_id')
            ->withPivot(['position'])
            ->orderBy('id', 'ASC');
    }

    public function taskInfo()
    {
        return $this->hasMany(PurchaseOrderHasTask::class, 'purchase_order_id', 'id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'purchase_orders_has_suppliers', 'purchase_order_id', 'supplier_id')->orderBy('id', 'ASC');
    }

    public function subcontractors()
    {
        return $this->belongsToMany(Subcontractor::class, 'purchase_orders_has_subcontractors', 'purchase_order_id', 'subcontractor_id')->orderBy('id', 'ASC');
    }

    public function subcontractorTaskInfo()
    {
        return $this->hasMany(PurchaseOrderHasSubcontractorTask::class, 'purchase_order_id', 'id');
    }

    public function subcontractorTaskPrice()
    {
        return $this->hasMany(SubcontractorTaskPrice::class, 'purchase_order_id', 'id')->where('qty', '!=', 0);
    }

    public function supplierPOData()
    {
        return $this->hasMany(SupplierPOData::class)->latest();
    }

    public function subcontractorPOData()
    {
        return $this->hasMany(SubcontractorPOData::class)->latest();
    }

    public function products()
    {
        return $this->belongsToMany(SupplierProduct::class, 'product_price_histories', 'purchase_order_id', 'product_id')
            ->withPivot(['id', 'name', 'desc', 'qty', 'unit', 'unit_price', 'total_price', 'product_no', 'status'])
            ->orderBy('product_price_histories.id', 'ASC');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn() => isset($this->po_number) ? $this->name . ' (PO No.: ' . $this->po_number . ')'
            : $this->name . ' (PO No.: N/A)',
        );
    }

}
