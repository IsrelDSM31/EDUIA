import React from 'react';

export function Card({ children, className = '' }) {
    return (
        <div className={`card bg-white overflow-hidden transition-all duration-200 hover:shadow-lg ${className}`}>
            {children}
        </div>
    );
}

export default Card; 