<?php
require_once("report.php");
class Delivery extends Report
{
	function __construct()
	{
		parent::__construct();
	}
	
	public function getDataColumns()
	{
		return array('summary' => array($this->lang->line('reports_sale_id'), $this->lang->line('reports_date'), $this->lang->line('reports_items_purchased'), $this->lang->line('reports_sold_by'), $this->lang->line('reports_sold_to'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'), $this->lang->line('reports_payment_type'), $this->lang->line('reports_delivery_time'), $this->lang->line('reports_zone'), $this->lang->line('reports_comments')),
					'details' => array($this->lang->line('reports_name'), $this->lang->line('reports_category'), $this->lang->line('reports_serial_number'), $this->lang->line('reports_description'), $this->lang->line('reports_quantity_purchased'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'),$this->lang->line('reports_discount'))
		);		
	}
	
	public function getData(array $inputs)
	{
		$this->db->select('sale_id, sale_date, sum(quantity_purchased) as items_purchased, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit, payment_type, delivery_time, zone, comment', false);
		$this->db->from('sales_items_temp');
		$this->db->join('people as employee', 'sales_items_temp.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales_items_temp.customer_id = customer.person_id', 'left');
		$this->db->where('delivery_date = "'. $inputs['delivery_date'].'"');
		$this->db->where('delivery_time = "'. $inputs['delivery_time'].'"');
		$this->db->group_by('sale_id');
		$this->db->order_by('zone, customer_id');

		$data = array();
		$data['summary'] = $this->db->get()->result_array();
		$data['details'] = array();
		
		foreach($data['summary'] as $key=>$value)
		{
			$this->db->select('name, category, quantity_purchased, serialnumber, sales_items_temp.description, subtotal,total, tax, profit, discount_percent');
			$this->db->from('sales_items_temp');
			$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
			$this->db->where('sale_id = '.$value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}
		
		return $data;
	}
	
	public function getSummaryData(array $inputs)
	{
		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->where('delivery_date = "'. $inputs['delivery_date'].'"');
		$this->db->where('delivery_time = "'. $inputs['delivery_time'].'"');
		
		return $this->db->get()->row_array();
	}
}
?>