import React, {useState} from "react";
import {getUrlParamValue} from "../../../utils";

const FilterCheckbox = (data) => {

	data = data.data ? data.data : data

	const label = data.label
	const name = data.name

	const urlParam = data.url

	const onChange = data.onChange

	const [isChecked, setIsChecked] = useState(!!getUrlParamValue(urlParam))

	return (
		<div className={'block'}>
			<input
				id={name}
				type={"checkbox"}
				checked={isChecked}
				name={name}
				className={'form-checkbox pl-3 h-5 w-5 text-indigo-600 focus:outline-none focus:ring focus:border-indogo-300 rounded'}
				onChange={(event) => {
					const checked = event.target.checked
					setIsChecked(checked)
					onChange(checked)
				}}
			/>
			<label htmlFor={name} className={'ml-2 text-sm font-medium text-gray-900'}>
				{label}
			</label>
		</div>
	)
}

export default FilterCheckbox