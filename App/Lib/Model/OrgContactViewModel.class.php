<?php
class OrgContactViewModel extends ViewModel {
	
	public $viewFields = array(
		'OrgContact'=> array('*'),
		'Org' 		=> array(
			'org_name'			=> 'org_name',
			'category_id_0'		=> 'category_id_0',
			'category_id_1'		=> 'category_id_1',
			'category_id_2'		=> 'category_id_2',
			'level_id'			=> 'level_id',
			'location_id'		=> 'location_id',
			'status'			=> 'status',
			'is_policy'			=> 'is_policy',
			'_type'				=> 'INNER',
			'_on'				=> 'Org.org_id=OrgContact.org_id'
		),
	);
	
}