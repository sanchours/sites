import React from 'react';
import {Button} from "antd";
import UilQuestionCircle from '@iconscout/react-unicons/icons/uil-question-circle';
import styles from './style.less';

export default ({ moduleData }) => {
  const handleClick = () => {

    const link = moduleData.getIn(['init', 'lang', 'link_help']);
    if (link)
      window.open(link,'_blank');

  };

  return (
    <Button
      className="top-nav_cache"
      onClick={handleClick}
    >
      <UilQuestionCircle size="20" />
    </Button>
  );
}
