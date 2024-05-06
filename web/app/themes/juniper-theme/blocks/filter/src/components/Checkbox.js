import React, { useState, useEffect } from "react"

const Checkbox = ({ label, isChecked, onChange }) => {
    const [checked, setChecked] = useState(isChecked);

    useEffect(() => {
        setChecked(isChecked);
    }, [isChecked]);

    const handleCheckboxChange = (e) => {
        const isChecked = e.target.checked;
        setChecked(isChecked);
        onChange(isChecked); // Pass the state back to the parent component
    };

    return (
        <div className="block">
            <input
                type="checkbox"
                checked={checked}
                onChange={handleCheckboxChange}
                className="form-checkbox h-5 w-5 text-indigo-600 focus:outline-none focus:ring focus:border-indigo-300 rounded"
            />
            <label
                className="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300"
            >
                {label}
            </label>
        </div>
    )
}

export default Checkbox;
