import React, {useEffect, useState} from "react";
import {getUrlParamValue} from "../../utils";

import Select, { components } from 'react-select';

import colorCodes from "../../ColorCodes";
import convert from "color-convert";
import translationObject from "../../TranslationObject";

const FilterDropdown = (data) => {

    data = data.data ? data.data : data

    const key = data.key
    const label = data.label
    const urlParam = data.url ?? ''
    const onChange = data.onChange

    const multiSelection = data.multiSelect ?? true

    const taxOptionsRaw = data.tax_options ?? []

    const [options, setOptions] = useState([]);

    const [values, setValues] = useState([]);

    function setDefaultSelectionFromUrl() {
        const urlParamValueRaw = getUrlParamValue(urlParam)

        const urlParamValues = urlParamValueRaw.split(',')

        const preSelectedOptions = options.filter((element) => {
            return urlParamValues.includes(element.slug)
        })

        const preSelectedOptionTermIds = preSelectedOptions.map((term) => term.term_id)

        setValues(preSelectedOptionTermIds)
    }

    /**
     * @param baseColor {String}
     * @returns {string}
     */
    function generateGradientCssTagForColor(baseColor) {
        const colorCodes = convert.hex.rgb(baseColor)

        const colorCodesJoin = colorCodes.join(',')

        return `linear-gradient(90deg, rgba(${colorCodesJoin},0) 0%, rgba(${colorCodesJoin},0.7581232322030375) 35%, rgba(${colorCodesJoin},1) 59%)`
    }

    function mapToOptionObject(tax) {
        const colorStyle = generateGradientCssTagForColor(colorCodes.getEntryWithSlugLike(tax.slug))

        return {label: tax.name, value: tax.term_id, colorStyle: colorStyle};
    }

    function addCategoryToOptions(newCategory) {
        setOptions((prevOptions) => {
            prevOptions.push(newCategory)
            return prevOptions
        })
    }

    function generateCategoryBaseConstruct(name) {
        return {
            label: name,
            options: []
        };
    }

    function generateCategoryOfParent(parent) {
        const newCategory = generateCategoryBaseConstruct(parent.name);

        newCategory.options = taxOptionsRaw
            .filter((tax) => tax.parent === parent.term_id || tax.term_id === parent.term_id)
            .map(mapToOptionObject)
            // sorts category head to top
            .sort((taxA, taxB) => taxA.label === mapToOptionObject(parent).label ? -1 : 1)

        return newCategory;
    }

    function prepareSelectorOptions() {
        const parents = taxOptionsRaw
            .filter((tax) => tax.parent)
            .map((tax) => tax.parent)
            .filter((tax, index, self) => self.indexOf(tax) === index)

        const parentTaxms = taxOptionsRaw.filter((tax) => parents.includes(tax.term_id))

        parentTaxms.forEach((parent) => {
            const category = generateCategoryOfParent(parent);

            addCategoryToOptions(category);
        })

        const othersCat = generateCategoryBaseConstruct('others');

        othersCat.options = taxOptionsRaw.filter(tax => !tax.parent && !parents.includes(tax.term_id)).map(mapToOptionObject)

        addCategoryToOptions(othersCat);
    }

    useEffect(() => {
        prepareSelectorOptions()
        setDefaultSelectionFromUrl();
    }, [])

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
            defaultValue={values}
            name={label}
            options={options}
            onChange={(newValue) => {
                onChange(newValue)
            }}
            placeholder={translationObject.select_label}
            components={{ Option }}
        />
    </div>
}

export default FilterDropdown