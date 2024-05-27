import React, {useEffect, useState} from "react";
import {getUrlParamValue} from "../../utils";

const FilterDropdown = (data) => {

	data = data.data ? data.data : data

	const key = data.key
	const label = data.label
	const name = data.name

	const [options, setOptions] = useState(data.tax_options ?? []);

	const urlParam = data.url ?? ''

	const onChange = data.onChange

	const [value, setValue] = useState('');

	useEffect(() => {
		onChange(value)
	}, [value]);

	useEffect(() => {
		const preSelectedOption = options.find((element) => {
			return element.slug === getUrlParamValue(urlParam)
		})

		setValue(preSelectedOption ? preSelectedOption.term_id : '')
	}, [])

	return <div key={key} className="relative w-full max-w-full mb-2.5">
		<label>{label}</label>
		<select
			value={value} // This will show the current state or "none" if undefined
			onChange={(event) => {
				const selected = event.target.value
				setValue(selected)
			}}
			className="select-filter block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-[0.95rem] pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline text-[#737373] text-sm"
		>
			<option value="">Wähle {label}</option>
			{options.map((term) => (
				<option key={term.term_id}
						value={term.term_id}>{term.name}</option>
			))}
		</select>
	</div>
}

export default FilterDropdown