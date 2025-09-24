import { useState, useEffect } from "react";

const Accordion = ({
  title,
  children,
  defaultOpen,
  display = true,
  displayHeader = true,
}) => {
  const [isOpen, setIsOpen] = useState(defaultOpen || false);
  useEffect(() => {
    setIsOpen(defaultOpen || false);
  }, [defaultOpen]);

  if (!display) {
    return null;
  }
  if (displayHeader === false) {
    return (
      <div className="accordion">
        <div className="accordion-content">{children}</div>
      </div>
    );
  }

  return (
    <div className="accordion">
      <button
        onClick={(e) => {
          e.preventDefault();
          setIsOpen(!isOpen);
        }}
        aria-expanded={isOpen}
        className={`accordion-header ${(isOpen ? "open" : "collapsed")}`}
      >
        <span>{title}</span>
        <div className="accordion-icon">{isOpen ? "−" : "+"}</div>
      </button>
      {isOpen && <div className="accordion-content">{children}</div>}
    </div>
  );
};

export default Accordion;
