import React, {useEffect, useState} from "react";
import {useInView} from "react-intersection-observer";

export default function SingleProduct(data) {

    const html = atob(data.htmlEnc ?? '');
    const index = data.index ?? 0
    const showDirectly = data.showDirectly ?? false

    const whenInView = data.whenInView ?? (() => {})

    const {ref, inView, entry} = useInView({
        threshold: 0,
        triggerOnce: true
    });

    const [displayClass, setDisplayClass] = useState(mapToDisplayClass(showDirectly));

    function mapToDisplayClass(shouldShow) {
        return shouldShow ? 'show' : 'hidden'
    }

    useEffect(() => {
        const displayClass = mapToDisplayClass(inView || showDirectly)

        whenInView()

        setDisplayClass(displayClass)
    }, [inView]);

    return (
        <div key={index}
             ref={ref}
             data-aos="fade-up"
             data-aos-delay={'200'}
             >
            <div
                className={'flex flex-col h-full col-span-3 sm:col-span-1 gap-y-14 sm:gap-y-0 flex-grow ' + displayClass}
                dangerouslySetInnerHTML={{__html: html}}
            />
        </div>)
}