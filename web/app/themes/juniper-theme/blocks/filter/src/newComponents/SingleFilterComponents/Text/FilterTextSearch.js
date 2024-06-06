import React, {useEffect, useState} from "react";
import {SearchGlassIcon} from "../../Icons";
import {getUrlParamValue} from "../../../utils";

const FilterTextSearch = (data) => {
	const label = data.label
	const name = data.name
	const urlParam = data.url
	const placeholder = data.placeholder
	const onChange = data.onChange
	
	const [value, setValue] = useState('')
	
	useEffect(() => {
		const paramFound = getUrlParamValue(urlParam)
		setValue(paramFound ?? '')
	}, []);
	
	useEffect(() => {
		onChange(value)
	}, [value]);
	
	return <div className={'flex items-center border-b py-2 col-span-12 mb-4 focus-visible:border-0'}>
		<SearchGlassIcon />
		<input
			className={'appearance-none bg-transparent border-none w-full text-primary mr-3 py-1 px-2 leading-tight focus:outline-none focus:shadow-none focus-visible:ring-transparent'}
			type={'text'}
			name={name}
			placeholder={placeholder}
			aria-label={label}
			value={value}
			onChange={(e) => {
				const newValue = e.target.value
				setValue(newValue)
			}}
		/>
	</div>
}

export default FilterTextSearch