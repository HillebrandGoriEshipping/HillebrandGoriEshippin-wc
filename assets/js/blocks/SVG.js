import { useEffect, useState } from 'react';

const SVG = ({ src, className = '', ...props }) => {
  const [svgContent, setSvgContent] = useState('');

  useEffect(() => {
    fetch(src)
      .then(res => res.text())
      .then(setSvgContent)
      .catch(console.error);
  }, [src]);

  return (
    <span
      className={className}
      {...props}
      dangerouslySetInnerHTML={{ __html: svgContent }}
    />
  );
}

export default SVG;