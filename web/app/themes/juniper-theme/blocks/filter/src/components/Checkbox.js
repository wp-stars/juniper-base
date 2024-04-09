import React, { useState, useEffect } from "react"

const Checkbox = ({ term, filterItem, handleTaxSelect }) => {
    const [checked, setChecked] = useState(false)    

    const handleCheckboxClick = (name, e) => {
        const isChecked = e.target.checked; // Check if the checkbox is checked
        setChecked(isChecked);
        // Call handleTaxSelect with appropriate parameters based on whether the checkbox is checked
        if (isChecked) {
            handleTaxSelect(name, e);
        } else {
            // If the checkbox is unchecked, pass "none" as the value
            handleTaxSelect(name, { target: { value: "none" } });
        }
    };
    return (
        <div className="block" >
            <input 
                name={term.name}
                value={term.term_id}
                onChange={(e) => handleCheckboxClick(filterItem.name, e)}
                type="checkbox"
                checked={checked}
                className="form-checkbox h-5 w-5 text-indigo-600 focus:outline-none focus:ring focus:border-indigo-300 rounded"

            />
            <label 
                htmlFor="default-checkbox" 
                className="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"
            >
                {term.name}
            </label>
        </div>
    )
}

export default Checkbox