import React, {useEffect, useState} from "react";

import chroma from 'chroma-js';

import Select, {components} from 'react-select';

import translationObject from "../../../TranslationObject";
import prepareDropdownOptions, {getDefaultSelectionFromUrl, preparePlaceholder} from "./utils";
import {clone, hideOptionName} from "../../../utils";

const FilterDropdown = (data) => {

    data = data.data ? data.data : data

    const key = data.key
    const label = preparePlaceholder(data.label, translationObject.select_label)
    const urlParam = data.url ?? ''
    const onChange = data.onChange

    const multiSelection = data.multiSelect ?? true

    const taxOptionsRaw = clone(data.tax_options) ?? []

    const _options = prepareDropdownOptions(taxOptionsRaw, label)
    const _preselectedValues = getDefaultSelectionFromUrl(urlParam, _options)

    const colourStyles = {
        control: (styles) => ({...styles, backgroundColor: 'white'}),
        option: (styles, {data, isDisabled, isFocused, isSelected}) => {
            const color = chroma(data.color);

            return {
                ...styles,
                backgroundColor: isDisabled
                    ? undefined
                    : isSelected
                        ? data.color
                        : isFocused
                            ? color.alpha(0.1).darken(5).css()
                            : undefined,

                color: isDisabled
                    ? '#ccc'
                    : chroma.contrast(color, 'white') > 2
                        ? 'white'
                        : 'black',

                cursor: isDisabled ? 'not-allowed' : 'default',

                ':active': {
                    ...styles[':active'],
                    backgroundColor: !isDisabled
                        ? isSelected
                            ? data.color
                            : color.alpha(0.5).css()
                        : undefined,
                },
            };
        },
        placeholder: (styles)=> ({
            ...styles,
            transition: 'color 100ms ease-in-out'
        }),
        container: (styles) => ({
            ...styles,
            ':hover div[class$="-placeholder"]': {
                color: 'black',
                fontWeight: '700',
            }
        }),

        multiValue: (styles, {data}) => {
            const color = chroma(data.color);

            return {
                ...styles,
                color: '#000',
                backgroundColor: color.alpha(0.1).css(),
                borderRadius: '3px',
            };
        },

        multiValueLabel: (styles, {data}) => {
            const color = chroma(data.color)

            const optionNameHidden = hideOptionName(data.slug, data.parent)

            const backgroundIsWhite = chroma.contrast(color, 'white') < 1.1

            const needsBorder = backgroundIsWhite && optionNameHidden

            const makeBackgroundBlandGray = backgroundIsWhite && !needsBorder

            return {
                ...styles,
                color: 'black',
                backgroundColor: makeBackgroundBlandGray ? '#e6e6e6' : color.css(),

                borderStyle: 'solid',
                borderWidth: needsBorder ? '1px' : '0',
                borderColor: needsBorder ? 'black' : 'none',

                paddingTop: needsBorder ? '2px' : styles.paddingTop,
                paddingBottom: needsBorder ? '2px' : styles.paddingBottom,

                paddingRight: '1rem',
                paddingLeft: '1rem',


            }
        },

        multiValueRemove: (styles, {data}) => {
            const color = chroma(data.color)

            return {
                ...styles,
                color: '#9f9f9f',
                backgroundColor: color.alpha(0.1).darken(5).css(),
                ':hover': {
                    backgroundColor: color.css(),
                    color: '#000',
                },
            }
        },
    };

    const customTheme = ((theme) => ({
        ...theme,
        colors: {
            ...theme.colors,
            primary: 'black',
        }
    }))

    useEffect(() => {
        onChange(_preselectedValues)
    }, []);

    const Option = (props) => {
        return (
            <div style={{background: props.data.colorStyle, fontWeight: '600'}}>
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
            placeholder={label}
            components={{Option}}
            styles={colourStyles}
            theme={customTheme}
        />
    </div>
}

export default FilterDropdown