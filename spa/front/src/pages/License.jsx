import { HashRouter, Routes, Route, Link } from 'react-router-dom';
import React from 'react';
import { licenseTabs } from '../data';

const License = () => {
    return (
        <>
            <HashRouter>
                <Routes>
                    {licenseTabs.map((route, index) => (
                        <Route
                            key={index}
                            path={route.path}
                            element={<route.element />}
                        />
                    ))}
                </Routes>
            </HashRouter>
        </>
    );
}

export default License;