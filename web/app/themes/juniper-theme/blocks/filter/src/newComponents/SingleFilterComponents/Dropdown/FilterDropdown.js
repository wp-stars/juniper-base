import React, {useEffect, useState} from "react";

import Select, {components} from 'react-select';

import translationObject from "../../../TranslationObject";
import prepareDropdownOptions, {getDefaultSelectionFromUrl} from "./utils";

const FilterDropdown = (data) => {

    data = data.data ? data.data : data

    const key = data.key
    const label = data.label
    const urlParam = data.url ?? ''
    const onChange = data.onChange

    const multiSelection = data.multiSelect ?? true

    const taxOptionsRaw = data.tax_options ?? []

    const _options= prepareDropdownOptions(taxOptionsRaw, label)
    const _preselectedValues = getDefaultSelectionFromUrl(urlParam, _options)

    useEffect(() => {
        onChange(_preselectedValues)
    }, []);

    const Option = (props) => {
        return (
            <div style={{background: props.data.colorStyle}}>
                <components.Option {...props} />
            </div>
        );
    };

    return <div key={key} className="relative w-full max-w-full mb-4">
        <label>{label}</label>
        <Select
            isMulti={multiSelection}
            defaultValue={_preselectedValues}
            name={label}
            options={_options}
            onChange={(newValue) => {
                onChange(newValue)
            }}
            placeholder={`${label} ${translationObject.select_label}`}
            components={{Option}}
        />
    </div>
}

export default FilterDropdown