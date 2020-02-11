import React, {useCallback} from 'react'
import Dropzone from 'react-dropzone'
import axios from 'axios';

/**
 * Lists out articles.
 */
class UploadBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      data: [],
      articleData: null,
      loaded: false,
      filedata: {},
      bucket: '',
    };
    this.handleReturnClick = this.handleReturnClick.bind(this)
    this.doUpload = this.doUpload.bind(this)
  }

  renderDropzone() {
    return <Dropzone onDrop={acceptedFiles => this.dropZoneCallback(acceptedFiles)}>
      {({getRootProps, getInputProps}) => (
        <section>
          <div {...getRootProps()}>
            <input {...getInputProps()} />
            <p>Drag 'n' drop some files here, or click to select files</p>
          </div>
        </section>
      )}
    </Dropzone>;
  }

  dropZoneCallback(acceptedFiles) {
    console.log(acceptedFiles)
    
    this.setState({filedata: {
      name: acceptedFiles[0].name,
      file: acceptedFiles[0]
    }})
  }

  /**
   * Load articles via Drupal JSON:API.
   */
  doUpload() {
    let bucket = document.getElementById('edit-field-bucket-0-target-id').value;
    let path = document.getElementById('edit-field-path-0-value').value;
    console.log(path);

    axios.post('/rest/session/token')
    .then(res => {
      console.log(this.state);
      let csrf_token = res.data;
      var dataPackage = {};
      dataPackage.operation = [{"value":'create'}];
      dataPackage.file_name = [{"value":this.state.filedata.name}];
      dataPackage.bucket = [{"value":'test-bucket-a4816'}];
      dataPackage.path_to_store = [{"value":path}];
      const headers = {
        'Content-Type': 'application/json',
        "Accept": "application/json",
        "X-CSRF-Token": csrf_token,
      }
      const data = JSON.stringify(dataPackage);
      axios.post("/aws-crr/v1/endpoint?_format=json", data, {
        headers: headers,
      })
      .then(res => {
        console.log(res);
        let fileurl = res.data.presigned_url
        axios.put(fileurl, this.state.filedata.file, {
          onUploadProgress: progressEvent => console.log('file progress', progressEvent.loaded),
          headers: { 'Content-Type': 'text/octet-stream' }
        })
        .then(res => {
          console.log(res);
        })
      })
    })
    // axios.post(`https://jsonplaceholder.typicode.com/users`, { user })
    // .then(res => {
    //   console.log(res);
    //   console.log(res.data);
    // })
  }

  /**
   * Add article data to state when clicking on an article link.
   *
   * @param article
   * @param e
   */
  handleClick(article, e) {
    e.preventDefault();
    this.setState({
      articleData : article
    });
  }

  /**
   * Returns article data from state and displays list.
   * @param e
   */
  handleReturnClick(e) {
    e.preventDefault();
    this.setState({
      articleData : null
    });
  }

  render() {
    const { data, filedata, loaded } = this.state;
    console.log(window.drupalSettings);
    return <div>
      {this.renderDropzone()}
      <div id="fileinfo">
        {filedata ? 
        <div>{filedata.name}</div>
        : <div>No file added</div>}
      </div>
      <div onClick={this.doUpload}>Upload</div>
    </div>;
  }
}

export default UploadBlock;