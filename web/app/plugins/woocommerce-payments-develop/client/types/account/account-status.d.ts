export type AccountStatus =
	| 'complete'
	| 'enabled'
	| 'pending_verification'
	| 'restricted_partially'
	| 'restricted'
	| 'restricted_soon'
	| 'requirements.past_due'
	| 'requirements.pending_verification'
	| 'listed'
	| 'platform_paused'
	| 'rejected.fraud'
	| 'rejected.listed'
	| 'rejected.terms_of_service'
	| 'rejected.other'
	| 'under_review'
	| 'other';
