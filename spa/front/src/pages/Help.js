import { HashRouter, Routes, Route, Link } from 'react-router-dom';
import React from 'react';
import { helpTabs } from '../data';

const Help = () => {
    return (
        <>
            <HashRouter>
                <Routes>
                    {helpTabs.map((route, index) => (
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

export default Help;