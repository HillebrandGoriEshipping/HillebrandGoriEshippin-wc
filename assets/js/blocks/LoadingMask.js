const { translate } = window.hges.i18n;
import clsx from "clsx";
const { Spinner } = window.wc.blocksComponents;

const LoadingMask = ({
  children,
  className,
  screenReaderLabel,
  showSpinner = false,
  isLoading = true,
}) => {
  return (
    <div
      className={clsx(className, {
        "wc-block-components-loading-mask": isLoading,
      }) }
    >
      {isLoading && showSpinner && <Spinner />}
      <div
        className={clsx({
          "wc-block-components-loading-mask__children": isLoading,
        }) }
        aria-hidden={isLoading}
      >
        {children}
      </div>
      {isLoading && (
        <span className="screen-reader-text">
          {screenReaderLabel || translate("Loading…") }
        </span>
      ) }
    </div>
  );
};

export default LoadingMask;
