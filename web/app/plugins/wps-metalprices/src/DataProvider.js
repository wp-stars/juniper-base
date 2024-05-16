import React, {createContext, useContext, useState} from 'react';
import {formatDateYYYYMMDD} from './functions';
import dayjs from 'dayjs'
import axios from 'axios'
import styles from './index.module.css'

const initialData = {
	prices: [],
	isLoading: false,
	selectedMetal: 'gold',
	selectedMetalUnit: '€/g',
	selectedRange: 365, //in days
	after: dayjs().subtract(1, 'year'),
	before: dayjs(),
	previewData: [],
	trend: 0,
};


export const DataContext = createContext(initialData);

export const DataProvider = (props) => {
	const [data, setData] = useState(initialData);
	const [isLoading, setIsLoading] = useState(false)

	axios.interceptors.request.use(function (config) {
		// UPDATE: Add this code to show global loading indicator
		document.body.classList.add('loading-indicator');
		setIsLoading(true)
		return config
	}, function (error) {
		setIsLoading(false)
		return Promise.reject(error);
	});
	
	axios.interceptors.response.use(function (response) {
		// UPDATE: Add this code to hide global loading indicator
		document.body.classList.remove('loading-indicator');
		setIsLoading(false)
		return response;
	}, function (error) {
		document.body.classList.remove('loading-indicator');
		setIsLoading(false)
		return Promise.reject(error);
	});

	return (
		<>
			{isLoading ? 
				<div className={styles.loadingElement}>
					Loading...
				</div>
			: null}
			<DataContext.Provider value={{data, setData}} {...props} />
		</>
	);
};
