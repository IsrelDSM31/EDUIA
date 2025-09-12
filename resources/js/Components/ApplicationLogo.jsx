export default function ApplicationLogo(props) {
    return (
        <svg
            width={props.width || 160}
            height={props.height || 160}
            {...props}
            viewBox="0 0 100 100"
            xmlns="http://www.w3.org/2000/svg"
        >
            {/* Book base */}
            <path
                d="M 20,30 L 80,30 L 80,80 L 20,80 C 15,80 10,75 10,70 L 10,40 C 10,35 15,30 20,30 Z"
                fill="#8B1538"
            />
            
            {/* Book pages */}
            <path
                d="M 20,35 L 75,35 L 75,75 L 20,75 C 17,75 15,72 15,70 L 15,40 C 15,37 17,35 20,35 Z"
                fill="white"
            />
            
            {/* Digital circuit lines */}
            <path
                d="M 30,45 L 45,45 L 45,55 L 65,55 M 65,45 L 65,65"
                stroke="#8B1538"
                strokeWidth="2"
                fill="none"
            />
            
            {/* Connection dots */}
            <circle cx="30" cy="45" r="2" fill="#8B1538" />
            <circle cx="45" cy="55" r="2" fill="#8B1538" />
            <circle cx="65" cy="55" r="2" fill="#8B1538" />
            
            {/* Text "IAEDU" */}
            <text
                x="50"
                y="90"
                textAnchor="middle"
                fontFamily="Arial"
                fontSize="18"
                fill="#8B1538"
                fontWeight="bold"
            >
                IAEDU
            </text>
        </svg>
    );
}
