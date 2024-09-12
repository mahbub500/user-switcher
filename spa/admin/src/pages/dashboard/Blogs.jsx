import { useState, useEffect } from 'react';
import parse from 'html-react-parser';
import axios from "axios";
import Loader from '../../components/Loader';
import { devHome } from '../../data';

const Blogs = () => {

	const [ posts, setPosts ] = useState([]);
	const [ loading, setLoading ] = useState(true);

	useEffect( () => {
	    axios.get( devHome + '/wp-json/wp/v2/posts?per_page=10&_fields[]=link&_fields[]=title').then((res) => {
	        setPosts(res.data);
	        setLoading(false);
	    });
	}, [] );

	const postsList = [];

	{ posts.map(post => {
	    postsList.push(
	        <li><a href={post.link} target="_blank">{parse(post.title.rendered)}</a></li>
	    )
	})}

    return (
    	<>
        { ! loading ? postsList : <Loader /> }
        </>
    );
};

export default Blogs;