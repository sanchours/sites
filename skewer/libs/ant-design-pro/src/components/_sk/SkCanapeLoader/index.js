import React from 'react';
import classNames from 'classnames'
import styles from './style.less';

export default ({ fadein=false, width=75, main=true, loadText="Loading..." }) => {
  return (
    <div
      className={
        classNames("sk-loader", {
          "sk-loader--main": main
        })
      }
    >
      <svg
        className={
          classNames("sk-loader__wrapper", {
            "sk-loader__wrapper--main": main
          })
        }
        style={{ width: `${width}px`, height: `${width}px`, }}
        xmlns="http://www.w3.org/2000/svg"
        width={200}
        height={200}
        viewBox="0 0 200 200"
        fill="none"
      >
        <circle cx={100} cy={100} r={100} fill="white" />
        <path
          className={
            classNames("sk-loader__tent", {
              "sk-loader__red": fadein
            })
          }
          d="M155.653 36.51C154.461 35.6597 153.211 34.8541 151.907 34.106C129.596 21.2261 101.071 28.8729 88.1881 51.1802L55.52 107.764C39.9292 94.9668 29.9775 75.5494 29.9775 53.8079C29.9775 52.948 30.0031 52.0881 30.0351 51.2377C45.3893 29.2341 70.8902 14.8325 99.7414 14.8325C121.275 14.8325 140.945 22.8501 155.916 36.0625L155.653 36.51Z"
          fill="#D22B2F"
        />
        <path
          className={
            classNames("sk-loader__tent", {
              "sk-loader__yellow": fadein
            })
          }
          d="M16.9958 83.0044C16.852 84.4685 16.7849 85.955 16.7849 87.4607C16.7849 113.221 37.6696 134.105 63.4326 134.105H128.769C125.486 154.009 113.642 172.333 94.8156 183.202C94.0676 183.633 93.3163 184.043 92.5619 184.442C65.8334 182.144 40.6074 167.263 26.1771 142.273C15.4134 123.623 12.5267 102.582 16.4812 83.0044H16.9958V83.0044Z"
          fill="#FDB933"
        />
        <path
          className={
            classNames("sk-loader__tent", {
              "sk-loader__black": fadein
            })
          }
          d="M126.581 179.832C127.917 179.221 129.24 178.541 130.532 177.792C152.842 164.913 160.489 136.381 147.609 114.074L114.944 57.4872C133.818 50.3807 155.611 51.474 174.44 62.3463C175.182 62.7779 175.92 63.2222 176.639 63.6762C188.017 87.9751 187.742 117.258 173.312 142.247C162.548 160.894 145.771 173.915 126.846 180.276L126.581 179.832Z"
          fill="#414042"
        />


      </svg>
      {loadText.length > 0 &&
        <div className="sk-loader__text">
          <h2>
            {loadText}
          </h2>
        </div>
      }
    </div>
  )
}

